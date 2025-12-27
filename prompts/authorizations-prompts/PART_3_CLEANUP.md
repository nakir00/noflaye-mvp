# 🚀 PROMPT CLAUDE CODE - PARTIE 3 : CLEANUP

> **Contexte** : Supprimer anciennes tables et colonnes obsolètes après migration vers nouvelle architecture

---

## 📋 OBJECTIF

Créer **1 fichier de migration** pour nettoyer toutes les anciennes structures devenues obsolètes après migration des données.

**Principe** : Suppression sécurisée avec backup recommendations et rollback complet.

---

## 🎯 CONTRAINTES STRICTES

### **Sécurité**
- ✅ **Vérifier données migrées** avant suppression
- ✅ **Transactions** pour atomicité
- ✅ **Backup recommendations** dans les commentaires
- ✅ **Rollback complet** (recréer tables avec structure)
- ✅ **Dry-run mode** optionnel (commenté)

### **Performance**
- ✅ **DROP TABLE** au lieu de DELETE (plus rapide)
- ✅ **DROP COLUMN** avec vérification existence
- ✅ **CASCADE** géré automatiquement
- ✅ Ordre de suppression respecté (FK)

### **Code Quality**
- ✅ PHPDoc exhaustif avec warnings
- ✅ Commentaires sur chaque suppression
- ✅ Progress indicators (echo)
- ✅ Validation avant/après
- ✅ < 250 lignes

---

## 📐 SPÉCIFICATION DÉTAILLÉE

### **MIGRATION : cleanup_old_permission_system_tables**

**Fichier** : `database/migrations/2025_12_26_200001_cleanup_old_permission_system_tables.php`

---

### **ÉTAPES DE CLEANUP**

#### **ÉTAPE 1 : Validation Pré-Cleanup**

Vérifier que migrations précédentes ont réussi :

```php
private function validateMigrationComplete(): void
{
    echo "🔍 Validating migration completion...\n";
    
    // Vérifier scopes créés
    $scopesCount = DB::table('scopes')->count();
    if ($scopesCount === 0) {
        throw new \Exception("No scopes found! Run migration 100001 first.");
    }
    echo "  ✓ Scopes: {$scopesCount} entries\n";
    
    // Vérifier templates créés
    $templatesCount = DB::table('permission_templates')->count();
    $rolesCount = DB::table('roles')->count();
    
    if ($templatesCount < $rolesCount) {
        throw new \Exception("Templates count ({$templatesCount}) < roles count ({$rolesCount}). Migration incomplete!");
    }
    echo "  ✓ Templates: {$templatesCount} entries (roles: {$rolesCount})\n";
    
    // Vérifier template_permissions créés
    $templatePermsCount = DB::table('template_permissions')->count();
    $rolePermsCount = DB::table('role_permissions')->count();
    
    if ($templatePermsCount < $rolePermsCount) {
        throw new \Exception("Template permissions count ({$templatePermsCount}) < role permissions count ({$rolePermsCount}). Migration incomplete!");
    }
    echo "  ✓ Template Permissions: {$templatePermsCount} entries (role perms: {$rolePermsCount})\n";
    
    // Vérifier user_templates créés
    $userTemplatesCount = DB::table('user_templates')->count();
    $userRolesCount = DB::table('user_roles')->count();
    
    if ($userTemplatesCount < $userRolesCount) {
        throw new \Exception("User templates count ({$userTemplatesCount}) < user roles count ({$userRolesCount}). Migration incomplete!");
    }
    echo "  ✓ User Templates: {$userTemplatesCount} entries (user roles: {$userRolesCount})\n";
    
    // Vérifier primary_template_id migré
    $usersWithPrimaryRole = DB::table('users')->whereNotNull('primary_role_id')->count();
    $usersWithPrimaryTemplate = DB::table('users')->whereNotNull('primary_template_id')->count();
    
    if ($usersWithPrimaryTemplate < $usersWithPrimaryRole) {
        throw new \Exception("Users with primary_template_id ({$usersWithPrimaryTemplate}) < users with primary_role_id ({$usersWithPrimaryRole}). Migration incomplete!");
    }
    echo "  ✓ Primary Template ID: {$usersWithPrimaryTemplate} users\n";
    
    echo "✅ All validations passed. Safe to cleanup.\n\n";
}
```

---

#### **ÉTAPE 2 : Backup Recommendations**

```php
private function showBackupRecommendations(): void
{
    echo "⚠️  BACKUP RECOMMENDATIONS\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Before proceeding, it is STRONGLY RECOMMENDED to backup:\n\n";
    
    echo "Tables to backup:\n";
    echo "  • roles\n";
    echo "  • role_permissions\n";
    echo "  • role_hierarchy\n";
    echo "  • user_roles\n";
    echo "  • default_permission_templates\n";
    echo "  • template_roles\n\n";
    
    echo "Backup commands:\n";
    echo "  mysqldump -u [user] -p [database] roles role_permissions role_hierarchy user_roles default_permission_templates template_roles > backup_old_permissions_$(date +%Y%m%d_%H%M%S).sql\n\n";
    
    echo "Press Ctrl+C to cancel or wait 5 seconds to continue...\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    // Optionnel : Attendre 5 secondes
    // sleep(5);
}
```

---

#### **ÉTAPE 3 : Supprimer Tables Pivot (ordre important)**

```php
echo "🗑️  Dropping pivot tables...\n";

// 1. template_roles (FK vers roles + default_permission_templates)
if (Schema::hasTable('template_roles')) {
    echo "  → Dropping template_roles...\n";
    Schema::dropIfExists('template_roles');
    echo "  ✓ Dropped template_roles\n";
}

// 2. role_permissions (FK vers roles + permissions)
if (Schema::hasTable('role_permissions')) {
    echo "  → Dropping role_permissions...\n";
    Schema::dropIfExists('role_permissions');
    echo "  ✓ Dropped role_permissions\n";
}

// 3. role_hierarchy (FK vers roles)
if (Schema::hasTable('role_hierarchy')) {
    echo "  → Dropping role_hierarchy...\n";
    Schema::dropIfExists('role_hierarchy');
    echo "  ✓ Dropped role_hierarchy\n";
}

// 4. user_roles (FK vers users + roles)
if (Schema::hasTable('user_roles')) {
    echo "  → Dropping user_roles...\n";
    Schema::dropIfExists('user_roles');
    echo "  ✓ Dropped user_roles\n";
}
```

---

#### **ÉTAPE 4 : Supprimer Tables Principales**

```php
echo "🗑️  Dropping main tables...\n";

// 5. roles (plus de FK vers cette table)
if (Schema::hasTable('roles')) {
    echo "  → Dropping roles...\n";
    Schema::dropIfExists('roles');
    echo "  ✓ Dropped roles\n";
}

// 6. default_permission_templates (plus de FK)
if (Schema::hasTable('default_permission_templates')) {
    echo "  → Dropping default_permission_templates...\n";
    Schema::dropIfExists('default_permission_templates');
    echo "  ✓ Dropped default_permission_templates\n";
}
```

---

#### **ÉTAPE 5 : Supprimer Colonnes Obsolètes**

```php
echo "🗑️  Dropping obsolete columns...\n";

// users.primary_role_id
if (Schema::hasColumn('users', 'primary_role_id')) {
    echo "  → Dropping users.primary_role_id...\n";
    Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['primary_role_id']);
        $table->dropColumn('primary_role_id');
    });
    echo "  ✓ Dropped users.primary_role_id\n";
}

// user_permissions.scope_type
if (Schema::hasColumn('user_permissions', 'scope_type')) {
    echo "  → Dropping user_permissions.scope_type...\n";
    Schema::table('user_permissions', function (Blueprint $table) {
        $table->dropColumn('scope_type');
    });
    echo "  ✓ Dropped user_permissions.scope_type\n";
}

// user_permissions.scope_id (ancienne colonne, pas la nouvelle)
// Note: On garde la NOUVELLE scope_id (FK vers scopes)
// On supprime uniquement si c'est l'ancienne structure
if (Schema::hasColumn('user_permissions', 'scope_id')) {
    // Vérifier si c'est la nouvelle (FK vers scopes) ou ancienne
    $hasForeignKey = DB::select("
        SELECT COUNT(*) as count 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'user_permissions'
        AND COLUMN_NAME = 'scope_id'
        AND REFERENCED_TABLE_NAME = 'scopes'
    ")[0]->count ?? 0;
    
    if ($hasForeignKey === 0) {
        // Ancienne colonne sans FK vers scopes
        echo "  → Dropping old user_permissions.scope_id...\n";
        Schema::table('user_permissions', function (Blueprint $table) {
            $table->dropColumn('scope_id');
        });
        echo "  ✓ Dropped old user_permissions.scope_id\n";
    } else {
        echo "  ℹ️  Keeping new user_permissions.scope_id (FK to scopes)\n";
    }
}

// user_group_members.scope_type
if (Schema::hasColumn('user_group_members', 'scope_type')) {
    echo "  → Dropping user_group_members.scope_type...\n";
    Schema::table('user_group_members', function (Blueprint $table) {
        $table->dropColumn('scope_type');
    });
    echo "  ✓ Dropped user_group_members.scope_type\n";
}

// user_group_members.scope_id (même logique que user_permissions)
if (Schema::hasColumn('user_group_members', 'scope_id')) {
    $hasForeignKey = DB::select("
        SELECT COUNT(*) as count 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'user_group_members'
        AND COLUMN_NAME = 'scope_id'
        AND REFERENCED_TABLE_NAME = 'scopes'
    ")[0]->count ?? 0;
    
    if ($hasForeignKey === 0) {
        echo "  → Dropping old user_group_members.scope_id...\n";
        Schema::table('user_group_members', function (Blueprint $table) {
            $table->dropColumn('scope_id');
        });
        echo "  ✓ Dropped old user_group_members.scope_id\n";
    } else {
        echo "  ℹ️  Keeping new user_group_members.scope_id (FK to scopes)\n";
    }
}
```

---

#### **ÉTAPE 6 : Validation Post-Cleanup**

```php
private function validateCleanupComplete(): void
{
    echo "\n🔍 Validating cleanup completion...\n";
    
    // Vérifier tables supprimées
    $droppedTables = [
        'roles',
        'role_permissions',
        'role_hierarchy',
        'user_roles',
        'default_permission_templates',
        'template_roles',
    ];
    
    foreach ($droppedTables as $table) {
        if (Schema::hasTable($table)) {
            throw new \Exception("Table {$table} still exists!");
        }
    }
    echo "  ✓ All old tables dropped\n";
    
    // Vérifier colonnes supprimées
    if (Schema::hasColumn('users', 'primary_role_id')) {
        throw new \Exception("Column users.primary_role_id still exists!");
    }
    echo "  ✓ users.primary_role_id dropped\n";
    
    if (Schema::hasColumn('user_permissions', 'scope_type')) {
        throw new \Exception("Column user_permissions.scope_type still exists!");
    }
    echo "  ✓ user_permissions.scope_type dropped\n";
    
    if (Schema::hasColumn('user_group_members', 'scope_type')) {
        throw new \Exception("Column user_group_members.scope_type still exists!");
    }
    echo "  ✓ user_group_members.scope_type dropped\n";
    
    // Vérifier nouvelles structures intactes
    $newTables = [
        'scopes',
        'permission_templates',
        'permission_wildcards',
        'template_permissions',
        'user_templates',
    ];
    
    foreach ($newTables as $table) {
        if (!Schema::hasTable($table)) {
            throw new \Exception("New table {$table} is missing!");
        }
    }
    echo "  ✓ All new tables intact\n";
    
    echo "✅ Cleanup validation passed\n";
}
```

---

### **STRUCTURE COMPLÈTE up()**

```php
public function up(): void
{
    echo "🧹 Starting cleanup of old permission system...\n\n";
    
    DB::transaction(function () {
        // Validation pré-cleanup
        $this->validateMigrationComplete();
        
        // Backup recommendations
        $this->showBackupRecommendations();
        
        // Suppression tables pivot
        echo "🗑️  Dropping pivot tables...\n";
        Schema::dropIfExists('template_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('role_hierarchy');
        Schema::dropIfExists('user_roles');
        echo "  ✓ Dropped 4 pivot tables\n\n";
        
        // Suppression tables principales
        echo "🗑️  Dropping main tables...\n";
        Schema::dropIfExists('roles');
        Schema::dropIfExists('default_permission_templates');
        echo "  ✓ Dropped 2 main tables\n\n";
        
        // Suppression colonnes obsolètes
        echo "🗑️  Dropping obsolete columns...\n";
        
        if (Schema::hasColumn('users', 'primary_role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['primary_role_id']);
                $table->dropColumn('primary_role_id');
            });
            echo "  ✓ Dropped users.primary_role_id\n";
        }
        
        if (Schema::hasColumn('user_permissions', 'scope_type')) {
            Schema::table('user_permissions', function (Blueprint $table) {
                $table->dropColumn('scope_type');
            });
            echo "  ✓ Dropped user_permissions.scope_type\n";
        }
        
        if (Schema::hasColumn('user_group_members', 'scope_type')) {
            Schema::table('user_group_members', function (Blueprint $table) {
                $table->dropColumn('scope_type');
            });
            echo "  ✓ Dropped user_group_members.scope_type\n";
        }
        
        echo "\n";
        
        // Validation post-cleanup
        $this->validateCleanupComplete();
    });
    
    echo "\n✅ Cleanup completed successfully!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Old permission system fully removed.\n";
    echo "New unified system is now active.\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
}
```

---

### **STRUCTURE COMPLÈTE down()**

**Important** : Rollback doit recréer structure complète (pour sécurité)

```php
public function down(): void
{
    echo "⚠️  ROLLING BACK: Recreating old permission system tables...\n\n";
    
    DB::transaction(function () {
        // Recréer roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();
            $table->string('color', 50)->nullable();
            $table->string('icon', 100)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Recréer role_permissions
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');
            $table->timestamps();
            $table->unique(['role_id', 'permission_id']);
        });
        
        // Recréer role_hierarchy
        Schema::create('role_hierarchy', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_role_id');
            $table->unsignedBigInteger('child_role_id');
            $table->timestamps();
            $table->unique(['parent_role_id', 'child_role_id']);
        });
        
        // Recréer user_roles
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
            $table->string('scope_type', 255)->nullable();
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->timestamps();
        });
        
        // Recréer default_permission_templates
        Schema::create('default_permission_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();
            $table->string('color', 50)->nullable();
            $table->string('icon', 100)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });
        
        // Recréer template_roles
        Schema::create('template_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('default_permission_template_id');
            $table->unsignedBigInteger('role_id');
            $table->timestamps();
        });
        
        // Recréer colonnes
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('primary_role_id')->nullable()->after('id');
        });
        
        Schema::table('user_permissions', function (Blueprint $table) {
            $table->string('scope_type', 255)->nullable()->after('permission_id');
        });
        
        Schema::table('user_group_members', function (Blueprint $table) {
            $table->string('scope_type', 255)->nullable()->after('user_group_id');
        });
        
        echo "✅ Old tables recreated (empty)\n";
        echo "⚠️  Note: Data NOT restored. Restore from backup if needed.\n";
    });
}
```

---

## ✅ CHECKLIST VALIDATION

- [ ] Validation pré-cleanup (counts comparison)
- [ ] Backup recommendations affichées
- [ ] Tables supprimées dans bon ordre (FK)
- [ ] Colonnes supprimées avec vérification
- [ ] Validation post-cleanup
- [ ] down() recrée structure complète
- [ ] Progress indicators (echo)
- [ ] Transaction atomique
- [ ] < 250 lignes

---

## 🚀 COMMANDE

**Génère le fichier de migration :**
```
database/migrations/2025_12_26_200001_cleanup_old_permission_system_tables.php
```

**Le fichier doit :**
1. Valider migration complète avant cleanup
2. Afficher backup recommendations
3. Supprimer tables dans bon ordre
4. Supprimer colonnes obsolètes
5. Valider cleanup complet
6. Avoir down() avec recréation structure
7. Progress indicators partout
8. Être production-safe

---

**GO ! 🎯**
