# 🚀 PROMPT CLAUDE CODE - PARTIE 1 : MIGRATIONS

> **Contexte** : Migration système de permissions Noflaye Box vers architecture optimisée avec toutes contributions

---

## 📋 OBJECTIF

Créer **15 fichiers de migrations séparés** pour faciliter la maintenance et le rollback granulaire.

Chaque migration = 1 responsabilité = 1 table ou 1 groupe logique.

---

## 🎯 CONTRAINTES STRICTES

### **Performance**
- ✅ Index sur toutes FK
- ✅ Index composites pour queries fréquentes
- ✅ Pas de boucles de requêtes
- ✅ Bulk operations uniquement

### **Qualité Code**
- ✅ Commentaires exhaustifs
- ✅ `comment()` sur chaque colonne importante
- ✅ Nommage explicite
- ✅ Foreign keys avec CASCADE approprié

### **Structure**
- ✅ 1 migration = 1 fichier
- ✅ Ordre d'exécution respecté (numérotation)
- ✅ `up()` et `down()` symétriques
- ✅ < 200 lignes par fichier

---

## 📁 LISTE DES 15 MIGRATIONS À CRÉER

### **Groupe 1 : Tables Fondamentales (1-3)**

```
database/migrations/2025_12_26_000001_create_scopes_table.php
database/migrations/2025_12_26_000002_create_permission_templates_table.php
database/migrations/2025_12_26_000003_create_permission_wildcards_table.php
```

### **Groupe 2 : Tables Pivot Wildcards (4-6)**

```
database/migrations/2025_12_26_000004_create_wildcard_pivots_tables.php
database/migrations/2025_12_26_000005_create_template_permissions_table.php
database/migrations/2025_12_26_000006_create_user_templates_table.php
```

### **Groupe 3 : Hiérarchies Calculées (7-9)**

```
database/migrations/2025_12_26_000007_create_permission_template_hierarchy_table.php
database/migrations/2025_12_26_000008_create_user_group_hierarchy_table.php
database/migrations/2025_12_26_000009_create_permission_group_hierarchy_table.php
```

### **Groupe 4 : Audit & Conformité (10-11)**

```
database/migrations/2025_12_26_000010_create_permission_audit_log_table.php
database/migrations/2025_12_26_000011_create_permission_rate_limits_table.php
```

### **Groupe 5 : Délégation (12-13)**

```
database/migrations/2025_12_26_000012_create_permission_delegations_table.php
database/migrations/2025_12_26_000013_create_delegation_chain_table.php
```

### **Groupe 6 : Versioning & Workflow (14-15)**

```
database/migrations/2025_12_26_000014_create_permission_template_versions_table.php
database/migrations/2025_12_26_000015_create_permission_requests_table.php
```

### **Groupe 7 : Modifications Tables Existantes (16-20)**

```
database/migrations/2025_12_26_000016_add_hierarchy_to_user_groups_table.php
database/migrations/2025_12_26_000017_add_hierarchy_to_permission_groups_table.php
database/migrations/2025_12_26_000018_add_scope_and_conditions_to_user_permissions_table.php
database/migrations/2025_12_26_000019_add_scope_to_user_group_members_table.php
database/migrations/2025_12_26_000020_add_primary_template_to_users_table.php
```

---

## 📐 SPÉCIFICATIONS DÉTAILLÉES

### **MIGRATION 1 : scopes**

```php
Schema::create('scopes', function (Blueprint $table) {
    $table->id();
    
    // Polymorphic
    $table->string('scopable_type', 255)->index();
    $table->unsignedBigInteger('scopable_id')->index();
    
    // Clé lisible
    $table->string('scope_key', 100)->unique()->comment('Format: type:id');
    
    // Métadonnées
    $table->string('name', 255)->nullable();
    $table->boolean('is_active')->default(true)->index();
    
    $table->timestamps();
    $table->softDeletes();
    
    // Index composites
    $table->index(['scopable_type', 'scopable_id'], 'idx_scopes_scopable');
    $table->index(['is_active', 'scopable_type'], 'idx_scopes_active_type');
    
    // Unique constraint
    $table->unique(['scopable_type', 'scopable_id'], 'unique_scopable');
});
```

### **MIGRATION 2 : permission_templates**

```php
Schema::create('permission_templates', function (Blueprint $table) {
    $table->id();
    
    // Identifiants
    $table->string('name', 255)->index();
    $table->string('slug', 255)->unique();
    $table->text('description')->nullable();
    
    // Hiérarchie
    $table->unsignedBigInteger('parent_id')->nullable();
    $table->foreign('parent_id')->references('id')->on('permission_templates')
        ->onDelete('set null')->onUpdate('cascade');
    
    // Scope par défaut
    $table->unsignedBigInteger('scope_id')->nullable();
    $table->foreign('scope_id')->references('id')->on('scopes')
        ->onDelete('set null')->onUpdate('cascade');
    
    // UI
    $table->string('color', 50)->default('primary');
    $table->string('icon', 100)->default('heroicon-o-shield-check');
    $table->integer('level')->default(0)->comment('Niveau hiérarchique calculé');
    $table->integer('sort_order')->default(0)->index();
    
    // États
    $table->boolean('is_active')->default(true)->index();
    $table->boolean('is_system')->default(false);
    $table->boolean('auto_sync_users')->default(true);
    
    $table->timestamps();
    $table->softDeletes();
    
    // Index
    $table->index(['is_active', 'is_system'], 'idx_templates_active_system');
    $table->index(['level', 'sort_order'], 'idx_templates_hierarchy');
});
```

### **MIGRATION 3 : permission_wildcards**

```php
Schema::create('permission_wildcards', function (Blueprint $table) {
    $table->id();
    
    $table->string('pattern', 255)->unique()->comment('Ex: shops.*, *.read');
    $table->text('description')->nullable();
    
    // Type
    $table->enum('pattern_type', ['full', 'resource', 'action', 'macro'])->default('full');
    // full: *.*
    // resource: shops.*
    // action: *.read
    // macro: shops.write
    
    // UI
    $table->string('icon', 100)->nullable();
    $table->string('color', 50)->default('primary');
    $table->integer('sort_order')->default(0)->index();
    
    // État
    $table->boolean('is_active')->default(true)->index();
    $table->boolean('auto_expand')->default(true);
    
    // Cache
    $table->timestamp('last_expanded_at')->nullable();
    $table->integer('permissions_count')->default(0);
    
    $table->timestamps();
    
    $table->index(['is_active', 'auto_expand'], 'idx_wildcards_active_expand');
});
```

### **MIGRATION 4 : wildcard_pivots**

```php
// wildcard_permissions (cache expansion)
Schema::create('wildcard_permissions', function (Blueprint $table) {
    $table->id();
    
    $table->unsignedBigInteger('wildcard_id');
    $table->foreign('wildcard_id')->references('id')->on('permission_wildcards')->onDelete('cascade');
    
    $table->unsignedBigInteger('permission_id');
    $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
    
    $table->boolean('is_auto_generated')->default(true);
    $table->timestamp('expanded_at')->useCurrent();
    
    $table->unique(['wildcard_id', 'permission_id'], 'unique_wildcard_permission');
    $table->index('wildcard_id');
    $table->index('permission_id');
});

// template_wildcards
Schema::create('template_wildcards', function (Blueprint $table) {
    $table->id();
    
    $table->unsignedBigInteger('template_id');
    $table->foreign('template_id')->references('id')->on('permission_templates')->onDelete('cascade');
    
    $table->unsignedBigInteger('wildcard_id');
    $table->foreign('wildcard_id')->references('id')->on('permission_wildcards')->onDelete('cascade');
    
    $table->integer('sort_order')->default(0);
    $table->timestamps();
    
    $table->unique(['template_id', 'wildcard_id'], 'unique_template_wildcard');
});
```

### **MIGRATION 5 : template_permissions**

```php
Schema::create('template_permissions', function (Blueprint $table) {
    $table->id();
    
    $table->unsignedBigInteger('template_id');
    $table->foreign('template_id')->references('id')->on('permission_templates')->onDelete('cascade');
    
    $table->unsignedBigInteger('permission_id');
    $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
    
    // Source
    $table->enum('source', ['direct', 'wildcard', 'inherited'])->default('direct');
    $table->unsignedBigInteger('wildcard_id')->nullable();
    $table->foreign('wildcard_id')->references('id')->on('permission_wildcards')->onDelete('set null');
    
    $table->integer('sort_order')->default(0);
    $table->timestamps();
    
    $table->unique(['template_id', 'permission_id'], 'unique_template_permission');
    $table->index(['source', 'wildcard_id'], 'idx_source_wildcard');
});
```

### **MIGRATION 6 : user_templates**

```php
Schema::create('user_templates', function (Blueprint $table) {
    $table->id();
    
    $table->unsignedBigInteger('user_id');
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    
    $table->unsignedBigInteger('template_id');
    $table->foreign('template_id')->references('id')->on('permission_templates')->onDelete('cascade');
    
    // Scope
    $table->unsignedBigInteger('scope_id')->nullable();
    $table->foreign('scope_id')->references('id')->on('scopes')->onDelete('cascade');
    
    // Versioning
    $table->integer('template_version')->nullable()->comment('null = latest');
    $table->boolean('auto_upgrade')->default(true);
    
    // Sync
    $table->boolean('auto_sync')->default(true);
    
    // Validité
    $table->timestamp('valid_from')->nullable();
    $table->timestamp('valid_until')->nullable();
    
    // Métadonnées
    $table->text('reason')->nullable();
    $table->unsignedBigInteger('granted_by')->nullable();
    $table->foreign('granted_by')->references('id')->on('users')->onDelete('set null');
    
    $table->timestamps();
    
    $table->unique(['user_id', 'template_id', 'scope_id'], 'unique_user_template_scope');
    $table->index(['template_version', 'auto_upgrade'], 'idx_versioning');
    $table->index(['valid_from', 'valid_until'], 'idx_validity');
});
```

### **MIGRATION 7-9 : Hiérarchies (Pattern identique)**

```php
// permission_template_hierarchy
Schema::create('permission_template_hierarchy', function (Blueprint $table) {
    $table->id();
    
    $table->unsignedBigInteger('ancestor_id');
    $table->foreign('ancestor_id')->references('id')->on('permission_templates')->onDelete('cascade');
    
    $table->unsignedBigInteger('descendant_id');
    $table->foreign('descendant_id')->references('id')->on('permission_templates')->onDelete('cascade');
    
    $table->integer('depth')->default(0)->comment('0=direct, 1=grand-child, etc');
    
    $table->unique(['ancestor_id', 'descendant_id'], 'unique_template_hierarchy');
    $table->index(['ancestor_id', 'depth'], 'idx_ancestor_depth');
    $table->index(['descendant_id', 'depth'], 'idx_descendant_depth');
});

// Même pattern pour user_group_hierarchy et permission_group_hierarchy
```

### **MIGRATION 10 : permission_audit_log**

```php
Schema::create('permission_audit_log', function (Blueprint $table) {
    $table->id();
    
    // Qui ?
    $table->unsignedBigInteger('user_id')->nullable();
    $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
    $table->string('user_name', 255)->nullable();
    $table->string('user_email', 255)->nullable();
    
    // Quoi ?
    $table->string('action', 50)->index();
    $table->string('permission_slug', 255)->index();
    $table->string('permission_name', 255)->nullable();
    
    // Source ?
    $table->string('source', 50)->index();
    $table->unsignedBigInteger('source_id')->nullable();
    $table->string('source_name', 255)->nullable();
    
    // Scope ?
    $table->unsignedBigInteger('scope_id')->nullable();
    $table->foreign('scope_id')->references('id')->on('scopes')->onDelete('set null');
    
    // Par qui ?
    $table->unsignedBigInteger('performed_by')->nullable();
    $table->foreign('performed_by')->references('id')->on('users')->onDelete('set null');
    $table->string('performed_by_name', 255)->nullable();
    
    // Pourquoi ?
    $table->text('reason')->nullable();
    $table->json('metadata')->nullable();
    
    // Où et quand ?
    $table->string('ip_address', 45')->nullable();
    $table->text('user_agent')->nullable();
    $table->timestamp('created_at')->useCurrent()->index();
    
    // Index composites
    $table->index(['user_id', 'created_at'], 'idx_user_date');
    $table->index(['permission_slug', 'created_at'], 'idx_permission_date');
    $table->index(['action', 'created_at'], 'idx_action_date');
    $table->index(['source', 'source_id'], 'idx_source');
});
```

### **MIGRATION 11 : permission_rate_limits**

```php
Schema::create('permission_rate_limits', function (Blueprint $table) {
    $table->id();
    
    $table->unsignedBigInteger('user_id');
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    
    $table->string('permission', 255);
    $table->string('ip_address', 45);
    $table->text('user_agent')->nullable();
    
    $table->timestamp('exceeded_at')->useCurrent();
    
    $table->index('user_id');
    $table->index('permission');
    $table->index('exceeded_at');
    $table->index(['user_id', 'permission', 'exceeded_at'], 'idx_user_permission_date');
});
```

### **MIGRATION 12 : permission_delegations**

```php
Schema::create('permission_delegations', function (Blueprint $table) {
    $table->id();
    
    // Qui délègue ?
    $table->unsignedBigInteger('delegator_id');
    $table->foreign('delegator_id')->references('id')->on('users')->onDelete('cascade');
    $table->string('delegator_name', 255);
    
    // À qui ?
    $table->unsignedBigInteger('delegatee_id');
    $table->foreign('delegatee_id')->references('id')->on('users')->onDelete('cascade');
    $table->string('delegatee_name', 255);
    
    // Quelle permission ?
    $table->unsignedBigInteger('permission_id');
    $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
    $table->string('permission_slug', 255);
    
    // Scope ?
    $table->unsignedBigInteger('scope_id')->nullable();
    $table->foreign('scope_id')->references('id')->on('scopes')->onDelete('cascade');
    
    // Validité OBLIGATOIRE
    $table->timestamp('valid_from')->useCurrent();
    $table->timestamp('valid_until')->nullable(false);
    
    // Re-délégation
    $table->boolean('can_redelegate')->default(false);
    $table->integer('max_redelegation_depth')->default(0);
    
    // Métadonnées
    $table->text('reason')->nullable();
    $table->json('metadata')->nullable();
    
    // Révocation
    $table->timestamp('revoked_at')->nullable()->index();
    $table->unsignedBigInteger('revoked_by')->nullable();
    $table->foreign('revoked_by')->references('id')->on('users')->onDelete('set null');
    $table->text('revocation_reason')->nullable();
    
    $table->timestamps();
    
    // Index
    $table->index('delegator_id');
    $table->index('delegatee_id');
    $table->index(['valid_from', 'valid_until'], 'idx_validity');
    $table->index(['delegatee_id', 'revoked_at', 'valid_until'], 'idx_active_delegations');
});
```

### **MIGRATION 13 : delegation_chain**

```php
Schema::create('delegation_chain', function (Blueprint $table) {
    $table->id();
    
    $table->unsignedBigInteger('delegation_id');
    $table->foreign('delegation_id')->references('id')->on('permission_delegations')->onDelete('cascade');
    
    $table->unsignedBigInteger('parent_delegation_id')->nullable();
    $table->foreign('parent_delegation_id')->references('id')->on('permission_delegations')->onDelete('cascade');
    
    $table->integer('depth')->default(0)->comment('0=original, 1=re-delegation');
    
    $table->index('delegation_id');
    $table->index('parent_delegation_id');
    $table->index(['delegation_id', 'depth'], 'idx_delegation_depth');
});
```

### **MIGRATION 14 : permission_template_versions**

```php
Schema::create('permission_template_versions', function (Blueprint $table) {
    $table->id();
    
    $table->unsignedBigInteger('template_id');
    $table->foreign('template_id')->references('id')->on('permission_templates')->onDelete('cascade');
    
    $table->integer('version');
    
    // Snapshot template
    $table->string('name', 255);
    $table->string('slug', 255);
    $table->text('description')->nullable();
    $table->unsignedBigInteger('parent_id')->nullable();
    $table->unsignedBigInteger('scope_id')->nullable();
    $table->string('color', 50)->nullable();
    $table->string('icon', 100)->nullable();
    $table->integer('level')->default(0);
    
    // Snapshots JSON
    $table->json('permissions_snapshot')->comment('[{id, slug, name}, ...]');
    $table->json('wildcards_snapshot')->nullable()->comment('[{id, pattern}, ...]');
    
    // Métadonnées version
    $table->string('version_name', 255)->nullable()->comment('Ex: v2.0 - Analytics');
    $table->text('changelog')->nullable();
    $table->boolean('is_stable')->default(false)->index();
    $table->boolean('is_published')->default(false)->index();
    
    // Audit
    $table->unsignedBigInteger('created_by')->nullable();
    $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
    $table->timestamp('created_at')->useCurrent();
    
    $table->timestamp('published_at')->nullable();
    $table->unsignedBigInteger('published_by')->nullable();
    $table->foreign('published_by')->references('id')->on('users')->onDelete('set null');
    
    $table->unique(['template_id', 'version'], 'unique_template_version');
    $table->index(['is_published', 'is_stable'], 'idx_published_stable');
});
```

### **MIGRATION 15 : permission_requests**

```php
Schema::create('permission_requests', function (Blueprint $table) {
    $table->id();
    
    $table->unsignedBigInteger('user_id');
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    
    $table->unsignedBigInteger('permission_id');
    $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
    
    $table->unsignedBigInteger('scope_id')->nullable();
    $table->foreign('scope_id')->references('id')->on('scopes')->onDelete('cascade');
    
    $table->text('reason');
    
    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
    
    $table->timestamp('requested_at')->useCurrent();
    $table->timestamp('reviewed_at')->nullable();
    $table->unsignedBigInteger('reviewed_by')->nullable();
    $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
    $table->text('review_comment')->nullable();
    
    $table->json('metadata')->nullable();
    $table->timestamps();
    
    $table->index(['user_id', 'status'], 'idx_user_status');
});
```

### **MIGRATIONS 16-20 : Modifications Tables Existantes**

**Migration 16 : user_groups**
```php
Schema::table('user_groups', function (Blueprint $table) {
    // Hiérarchie
    $table->unsignedBigInteger('parent_id')->nullable()->after('id');
    $table->foreign('parent_id')->references('id')->on('user_groups')
        ->onDelete('set null')->onUpdate('cascade');
    $table->integer('level')->default(0)->after('parent_id');
    
    // Template
    $table->unsignedBigInteger('template_id')->nullable()->after('level');
    $table->foreign('template_id')->references('id')->on('permission_templates')
        ->onDelete('set null')->onUpdate('cascade');
    $table->boolean('auto_sync_template')->default(false)->after('template_id');
    
    $table->index('parent_id');
    $table->index('template_id');
    $table->index(['level', 'parent_id'], 'idx_hierarchy');
});
```

**Migration 17 : permission_groups**
```php
Schema::table('permission_groups', function (Blueprint $table) {
    $table->unsignedBigInteger('parent_id')->nullable()->after('id');
    $table->foreign('parent_id')->references('id')->on('permission_groups')
        ->onDelete('set null')->onUpdate('cascade');
    $table->integer('level')->default(0)->after('parent_id');
    
    $table->index('parent_id');
    $table->index(['level', 'parent_id'], 'idx_hierarchy');
});
```

**Migration 18 : user_permissions**
```php
Schema::table('user_permissions', function (Blueprint $table) {
    // Scope unifié
    $table->unsignedBigInteger('scope_id')->nullable()->after('permission_id');
    $table->foreign('scope_id')->references('id')->on('scopes')->onDelete('cascade');
    
    // Conditions JSON
    $table->json('conditions')->nullable()->after('scope_id');
    
    // Source
    $table->string('source', 50)->default('direct')->after('conditions');
    $table->unsignedBigInteger('source_id')->nullable()->after('source');
    
    $table->index('scope_id');
    $table->index(['source', 'source_id'], 'idx_source');
});
```

**Migration 19 : user_group_members**
```php
Schema::table('user_group_members', function (Blueprint $table) {
    $table->unsignedBigInteger('scope_id')->nullable()->after('user_group_id');
    $table->foreign('scope_id')->references('id')->on('scopes')->onDelete('cascade');
    
    $table->index('scope_id');
});
```

**Migration 20 : users**
```php
Schema::table('users', function (Blueprint $table) {
    $table->unsignedBigInteger('primary_template_id')->nullable()->after('id');
    $table->foreign('primary_template_id')->references('id')->on('permission_templates')
        ->onDelete('set null');
    
    $table->index('primary_template_id');
});
```

---

## ✅ CHECKLIST VALIDATION

Pour chaque migration, vérifier :

- [ ] Header PHPDoc complet
- [ ] Commentaires `->comment()` sur colonnes clés
- [ ] Index sur toutes FK
- [ ] Index composites pour queries fréquentes
- [ ] Contraintes unique appropriées
- [ ] CASCADE/SET NULL approprié
- [ ] `up()` et `down()` symétriques
- [ ] < 200 lignes

---

## 🚀 COMMANDE

**Génère les 20 fichiers de migration dans :**
```
database/migrations/
```

**Nomenclature stricte :**
```
2025_12_26_000001_create_scopes_table.php
2025_12_26_000002_create_permission_templates_table.php
...
2025_12_26_000020_add_primary_template_to_users_table.php
```

**Chaque fichier doit :**
1. Être autonome et testable
2. Avoir PHPDoc exhaustif
3. Respecter les spécifications ci-dessus
4. Être production-ready

---

**GO ! 🎯**
