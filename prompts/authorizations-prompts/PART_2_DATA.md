# 🚀 PROMPT CLAUDE CODE - PARTIE 2 : MIGRATION DONNÉES

> **Contexte** : Migrer données existantes (roles, user_roles, etc.) vers nouvelle architecture (templates, scopes, hiérarchies)

---

## 📋 OBJECTIF

Créer **6 fichiers de migrations** pour migrer toutes les données existantes vers la nouvelle architecture sans perte de données.

**Principe** : Chaque migration = 1 tâche spécifique avec rollback complet.

---

## 🎯 CONTRAINTES STRICTES

### **Performance**
- ✅ **ZERO boucle de requêtes** (foreach avec query = INTERDIT)
- ✅ **Bulk operations** uniquement (INSERT SELECT, UPDATE JOIN)
- ✅ **Chunks** pour grandes tables (1000 lignes max par batch)
- ✅ Transactions pour atomicité

### **Sécurité Données**
- ✅ Vérifier existence avant migration
- ✅ Logs des erreurs
- ✅ Compteurs de validation (avant/après)
- ✅ Rollback complet en cas d'échec

### **Code Quality**
- ✅ PHPDoc exhaustif
- ✅ Commentaires sur logique complexe
- ✅ Progress indicators (echo pour CLI)
- ✅ < 250 lignes par fichier

---

## 📁 LISTE DES 6 MIGRATIONS À CRÉER

```
database/migrations/2025_12_26_100001_create_scopes_from_existing_data.php
database/migrations/2025_12_26_100002_migrate_roles_to_templates.php
database/migrations/2025_12_26_100003_migrate_default_templates_to_templates.php
database/migrations/2025_12_26_100004_migrate_role_permissions_to_template_permissions.php
database/migrations/2025_12_26_100005_migrate_user_roles_to_user_templates.php
database/migrations/2025_12_26_100006_rebuild_all_hierarchies.php
```

---

## 📐 SPÉCIFICATIONS DÉTAILLÉES

### **MIGRATION 1 : create_scopes_from_existing_data**

**Objectif** : Créer scopes depuis toutes les utilisations de scope_type + scope_id

**Sources de données** :
- `user_permissions` (scope_type, scope_id)
- `user_roles` (scope_type, scope_id) 
- `user_group_members` (scope_type, scope_id)
- Tables d'entités : `shops`, `kitchens`, `drivers`, `supervisors`, `suppliers`

**Algorithme** :
```php
// ÉTAPE 1 : Extraire TOUS les (scope_type, scope_id) uniques
$scopesToCreate = collect();

// Source 1 : user_permissions
$userPermScopes = DB::table('user_permissions')
    ->select('scope_type', 'scope_id')
    ->whereNotNull('scope_type')
    ->whereNotNull('scope_id')
    ->distinct()
    ->get();

$scopesToCreate = $scopesToCreate->merge($userPermScopes);

// Source 2 : user_roles
$userRolesScopes = DB::table('user_roles')
    ->select('scope_type', 'scope_id')
    ->whereNotNull('scope_type')
    ->whereNotNull('scope_id')
    ->distinct()
    ->get();

$scopesToCreate = $scopesToCreate->merge($userRolesScopes);

// Source 3 : user_group_members
$groupMembersScopes = DB::table('user_group_members')
    ->select('scope_type', 'scope_id')
    ->whereNotNull('scope_type')
    ->whereNotNull('scope_id')
    ->distinct()
    ->get();

$scopesToCreate = $scopesToCreate->merge($groupMembersScopes);

// ÉTAPE 2 : Dédupliquer
$uniqueScopes = $scopesToCreate->unique(function($item) {
    return $item->scope_type . ':' . $item->scope_id;
});

// ÉTAPE 3 : Préparer bulk insert avec noms
$inserts = [];
foreach ($uniqueScopes as $scope) {
    // Récupérer nom depuis table source (shops, kitchens, etc.)
    $name = $this->getScopeName($scope->scope_type, $scope->scope_id);
    
    $inserts[] = [
        'scopable_type' => $scope->scope_type,
        'scopable_id' => $scope->scope_id,
        'scope_key' => $this->makeScopeKey($scope->scope_type, $scope->scope_id),
        'name' => $name,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ];
}

// ÉTAPE 4 : Bulk insert (chunked pour performance)
DB::table('scopes')->insert($inserts);

// ÉTAPE 5 : Validation
$expected = $uniqueScopes->count();
$actual = DB::table('scopes')->count();
if ($expected !== $actual) {
    throw new \Exception("Scope creation mismatch: expected {$expected}, got {$actual}");
}
```

**Helper Methods** :
```php
private function getScopeName(string $type, int $id): ?string
{
    // Mapper scope_type vers table
    $table = match($type) {
        'App\\Models\\Shop' => 'shops',
        'App\\Models\\Kitchen' => 'kitchens',
        'App\\Models\\Driver' => 'drivers',
        'App\\Models\\Supervisor' => 'supervisors',
        'App\\Models\\Supplier' => 'suppliers',
        default => null,
    };
    
    if (!$table) return null;
    
    return DB::table($table)->where('id', $id)->value('name');
}

private function makeScopeKey(string $type, int $id): string
{
    // Extraire type court (Shop -> shop)
    $shortType = strtolower(class_basename($type));
    return "{$shortType}:{$id}";
}
```

**Validation** :
```php
echo "✅ Created " . DB::table('scopes')->count() . " scopes\n";
```

---

### **MIGRATION 2 : migrate_roles_to_templates**

**Objectif** : Migrer table `roles` → `permission_templates`

**Algorithme** :
```php
// ÉTAPE 1 : Récupérer tous les roles
$roles = DB::table('roles')->get();

// ÉTAPE 2 : Préparer bulk insert
$inserts = [];
foreach ($roles as $role) {
    $inserts[] = [
        'id' => $role->id, // Conserver ID pour éviter casse FK
        'name' => $role->name,
        'slug' => $role->slug ?? Str::slug($role->name),
        'description' => $role->description,
        'parent_id' => null, // Sera géré par migration 6 (rebuild hierarchies)
        'scope_id' => null,
        'color' => $role->color ?? 'primary',
        'icon' => $role->icon ?? 'heroicon-o-shield-check',
        'level' => 0, // Sera recalculé par migration 6
        'sort_order' => $role->sort_order ?? 0,
        'is_active' => $role->is_active ?? true,
        'is_system' => $role->is_system ?? false,
        'auto_sync_users' => true,
        'created_at' => $role->created_at,
        'updated_at' => $role->updated_at,
        'deleted_at' => $role->deleted_at ?? null,
    ];
}

// ÉTAPE 3 : Bulk insert
DB::table('permission_templates')->insert($inserts);

// ÉTAPE 4 : Validation
$expected = DB::table('roles')->count();
$actual = DB::table('permission_templates')
    ->whereIn('id', DB::table('roles')->pluck('id'))
    ->count();

if ($expected !== $actual) {
    throw new \Exception("Role migration mismatch");
}
```

**Validation** :
```php
echo "✅ Migrated {$expected} roles to templates\n";
```

---

### **MIGRATION 3 : migrate_default_templates_to_templates**

**Objectif** : Migrer `default_permission_templates` → `permission_templates`

**Attention** : Éviter collision ID avec roles migrés

**Algorithme** :
```php
// ÉTAPE 1 : Récupérer max ID des templates (roles migrés)
$maxId = DB::table('permission_templates')->max('id') ?? 0;

// ÉTAPE 2 : Récupérer default templates
$defaultTemplates = DB::table('default_permission_templates')->get();

// ÉTAPE 3 : Préparer inserts avec nouveaux IDs
$inserts = [];
$idMapping = []; // Ancien ID → Nouveau ID

foreach ($defaultTemplates as $template) {
    $newId = ++$maxId;
    $idMapping[$template->id] = $newId;
    
    $inserts[] = [
        'id' => $newId,
        'name' => $template->name,
        'slug' => $template->slug ?? Str::slug($template->name),
        'description' => $template->description,
        'parent_id' => null,
        'scope_id' => null,
        'color' => $template->color ?? 'primary',
        'icon' => $template->icon ?? 'heroicon-o-clipboard-list',
        'level' => 0,
        'sort_order' => $template->sort_order ?? 0,
        'is_active' => $template->is_active ?? true,
        'is_system' => $template->is_system ?? true,
        'auto_sync_users' => true,
        'created_at' => $template->created_at,
        'updated_at' => $template->updated_at,
    ];
}

// ÉTAPE 4 : Bulk insert
DB::table('permission_templates')->insert($inserts);

// ÉTAPE 5 : Stocker mapping pour prochaines migrations
cache()->put('default_template_id_mapping', $idMapping, 3600);
```

**Validation** :
```php
echo "✅ Migrated {$defaultTemplates->count()} default templates\n";
echo "ℹ️  ID mapping cached for next migrations\n";
```

---

### **MIGRATION 4 : migrate_role_permissions_to_template_permissions**

**Objectif** : Migrer `role_permissions` → `template_permissions`

**Algorithme** :
```php
// ÉTAPE 1 : Récupérer role_permissions (BULK)
$rolePermissions = DB::table('role_permissions')->get();

// ÉTAPE 2 : Préparer bulk insert
$inserts = [];
foreach ($rolePermissions as $rp) {
    $inserts[] = [
        'template_id' => $rp->role_id, // role_id = template_id (conservé)
        'permission_id' => $rp->permission_id,
        'source' => 'direct',
        'wildcard_id' => null,
        'sort_order' => 0,
        'created_at' => $rp->created_at ?? now(),
        'updated_at' => $rp->updated_at ?? now(),
    ];
}

// ÉTAPE 3 : Bulk insert (chunked)
foreach (array_chunk($inserts, 1000) as $chunk) {
    DB::table('template_permissions')->insert($chunk);
}

// ÉTAPE 4 : Migrer template_roles (default templates)
$idMapping = cache()->get('default_template_id_mapping', []);
$templateRoles = DB::table('template_roles')->get();

$templateInserts = [];
foreach ($templateRoles as $tr) {
    $newTemplateId = $idMapping[$tr->default_permission_template_id] ?? null;
    
    if (!$newTemplateId) {
        continue; // Skip si mapping introuvable
    }
    
    // Récupérer permissions du role_id
    $rolePermissions = DB::table('role_permissions')
        ->where('role_id', $tr->role_id)
        ->get();
    
    foreach ($rolePermissions as $rp) {
        $templateInserts[] = [
            'template_id' => $newTemplateId,
            'permission_id' => $rp->permission_id,
            'source' => 'inherited',
            'wildcard_id' => null,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

foreach (array_chunk($templateInserts, 1000) as $chunk) {
    DB::table('template_permissions')->insert($chunk);
}
```

**Validation** :
```php
$total = $rolePermissions->count() + count($templateInserts);
echo "✅ Migrated {$total} template-permission associations\n";
```

---

### **MIGRATION 5 : migrate_user_roles_to_user_templates**

**Objectif** : Migrer `user_roles` → `user_templates` avec scopes

**Algorithme** :
```php
// ÉTAPE 1 : Récupérer user_roles
$userRoles = DB::table('user_roles')->get();

// ÉTAPE 2 : Pour chaque user_role, trouver scope_id correspondant
$inserts = [];

foreach ($userRoles as $ur) {
    // Trouver scope_id depuis scopes table
    $scopeId = null;
    
    if ($ur->scope_type && $ur->scope_id) {
        $scopeId = DB::table('scopes')
            ->where('scopable_type', $ur->scope_type)
            ->where('scopable_id', $ur->scope_id)
            ->value('id');
    }
    
    $inserts[] = [
        'user_id' => $ur->user_id,
        'template_id' => $ur->role_id, // role_id = template_id
        'scope_id' => $scopeId,
        'template_version' => null,
        'auto_upgrade' => true,
        'auto_sync' => true,
        'valid_from' => $ur->valid_from,
        'valid_until' => $ur->valid_until,
        'reason' => null,
        'granted_by' => null,
        'created_at' => $ur->created_at ?? now(),
        'updated_at' => $ur->updated_at ?? now(),
    ];
}

// ÉTAPE 3 : Bulk insert (chunked)
foreach (array_chunk($inserts, 1000) as $chunk) {
    DB::table('user_templates')->insert($chunk);
}

// ÉTAPE 4 : Migrer users.primary_role_id → primary_template_id
DB::table('users')
    ->whereNotNull('primary_role_id')
    ->update([
        'primary_template_id' => DB::raw('primary_role_id'),
    ]);
```

**Validation** :
```php
$expected = $userRoles->count();
$actual = DB::table('user_templates')->count();
echo "✅ Migrated {$actual} user-template assignments\n";

$updatedUsers = DB::table('users')->whereNotNull('primary_template_id')->count();
echo "✅ Updated {$updatedUsers} users with primary_template_id\n";
```

---

### **MIGRATION 6 : rebuild_all_hierarchies**

**Objectif** : Recalculer TOUTES les hiérarchies (templates, groups, permission_groups)

**Algorithme** :
```php
// ÉTAPE 1 : Rebuild permission_template_hierarchy
$this->rebuildTemplateHierarchy();

// ÉTAPE 2 : Rebuild user_group_hierarchy
$this->rebuildUserGroupHierarchy();

// ÉTAPE 3 : Rebuild permission_group_hierarchy
$this->rebuildPermissionGroupHierarchy();

// ÉTAPE 4 : Recalculer levels
$this->recalculateLevels();

private function rebuildTemplateHierarchy(): void
{
    // Vider table
    DB::table('permission_template_hierarchy')->truncate();
    
    // Récupérer tous templates avec parent
    $templates = DB::table('permission_templates')
        ->select('id', 'parent_id')
        ->whereNotNull('parent_id')
        ->get();
    
    $inserts = [];
    
    foreach ($templates as $template) {
        // Trouver tous les ancêtres
        $ancestors = $this->findAncestors('permission_templates', $template->id);
        
        foreach ($ancestors as $depth => $ancestorId) {
            $inserts[] = [
                'ancestor_id' => $ancestorId,
                'descendant_id' => $template->id,
                'depth' => $depth,
            ];
        }
    }
    
    // Bulk insert
    if (!empty($inserts)) {
        DB::table('permission_template_hierarchy')->insert($inserts);
    }
}

private function findAncestors(string $table, int $id, int $depth = 0): array
{
    $ancestors = [];
    
    $parent = DB::table($table)
        ->where('id', $id)
        ->value('parent_id');
    
    if ($parent) {
        $ancestors[$depth] = $parent;
        $ancestors = array_merge($ancestors, $this->findAncestors($table, $parent, $depth + 1));
    }
    
    return $ancestors;
}

private function recalculateLevels(): void
{
    // Templates
    $templates = DB::table('permission_templates')->get();
    foreach ($templates as $template) {
        $level = DB::table('permission_template_hierarchy')
            ->where('descendant_id', $template->id)
            ->max('depth') ?? 0;
        
        DB::table('permission_templates')
            ->where('id', $template->id)
            ->update(['level' => $level]);
    }
    
    // User groups (même logique)
    $groups = DB::table('user_groups')->whereNotNull('parent_id')->get();
    foreach ($groups as $group) {
        $level = DB::table('user_group_hierarchy')
            ->where('descendant_id', $group->id)
            ->max('depth') ?? 0;
        
        DB::table('user_groups')
            ->where('id', $group->id)
            ->update(['level' => $level]);
    }
    
    // Permission groups (même logique)
    $permGroups = DB::table('permission_groups')->whereNotNull('parent_id')->get();
    foreach ($permGroups as $group) {
        $level = DB::table('permission_group_hierarchy')
            ->where('descendant_id', $group->id)
            ->max('depth') ?? 0;
        
        DB::table('permission_groups')
            ->where('id', $group->id)
            ->update(['level' => $level]);
    }
}
```

**Validation** :
```php
echo "✅ Rebuilt template hierarchy: " . DB::table('permission_template_hierarchy')->count() . " entries\n";
echo "✅ Rebuilt user group hierarchy: " . DB::table('user_group_hierarchy')->count() . " entries\n";
echo "✅ Rebuilt permission group hierarchy: " . DB::table('permission_group_hierarchy')->count() . " entries\n";
```

---

## ✅ CHECKLIST VALIDATION

Pour chaque migration :

- [ ] PHPDoc complet avec objectif
- [ ] ZERO foreach avec query
- [ ] Bulk operations (INSERT SELECT, chunked)
- [ ] Transactions si nécessaire
- [ ] Validation counts (before/after)
- [ ] Progress indicators (echo)
- [ ] Rollback complet dans down()
- [ ] < 250 lignes

---

## 🚀 COMMANDE

**Génère les 6 fichiers de migration dans :**
```
database/migrations/
```

**Nomenclature stricte :**
```
2025_12_26_100001_create_scopes_from_existing_data.php
2025_12_26_100002_migrate_roles_to_templates.php
2025_12_26_100003_migrate_default_templates_to_templates.php
2025_12_26_100004_migrate_role_permissions_to_template_permissions.php
2025_12_26_100005_migrate_user_roles_to_user_templates.php
2025_12_26_100006_rebuild_all_hierarchies.php
```

**Chaque fichier doit :**
1. Utiliser bulk operations UNIQUEMENT
2. Avoir validation counts
3. Logger progress (echo)
4. Être réversible (down())
5. Être production-safe

---

**GO ! 🎯**
