# 🔍 RAPPORT ANALYSE MIGRATIONS - NOFLAYE BOX

**Date** : 2025-12-27
**Projet** : Noflaye Box MVP
**Objectif** : Nettoyage migrations obsolètes après migration système RBAC → Templates hiérarchiques

---

## 📊 STATISTIQUES

- **Total migrations analysées** : 61
- **Migrations à garder** : 44 (72%)
- **Migrations à supprimer** : 14 (23%)
- **Migrations à vérifier** : 3 (5%)

---

## ✅ MIGRATIONS À GARDER (44 fichiers)

### **Laravel Core (3 migrations)**

1. **0001_01_01_000000_create_users_table.php**
   - Tables: `users`, `password_reset_tokens`, `sessions`
   - Raison: Tables core Laravel authentication - essentielles

2. **0001_01_01_000001_create_cache_table.php**
   - Tables: `cache`, `cache_locks`
   - Raison: Système de cache Laravel - essentiel

3. **0001_01_01_000002_create_jobs_table.php**
   - Tables: `jobs`, `job_batches`, `failed_jobs`
   - Raison: Système de queues Laravel - essentiel

---

### **Entités Business (5 migrations)**

4. **2025_12_21_121507_create_shops_table.php**
   - Table: `shops`
   - Raison: Entité business core

5. **2025_12_21_121507_create_suppliers_table.php**
   - Table: `suppliers`
   - Raison: Entité business core

6. **2025_12_21_231952_create_supervisors_table.php**
   - Table: `supervisors`
   - Raison: Entité business core

7. **2025_12_21_232023_create_kitchens_table.php**
   - Table: `kitchens`
   - Raison: Entité business core

8. **2025_12_21_232059_create_drivers_table.php**
   - Table: `drivers`
   - Raison: Entité business core

---

### **Tables Pivot Multi-Panel (11 migrations)**

9. **2025_12_21_125154_create_shop_user_table.php** - `shop_user`
10. **2025_12_21_125154_create_supplier_user_table.php** - `supplier_user`
11. **2025_12_21_232247_create_supervisor_user_table.php** - `supervisor_user`
12. **2025_12_21_232326_create_kitchen_user_table.php** - `kitchen_user`
13. **2025_12_21_232405_create_driver_user_table.php** - `driver_user`
14. **2025_12_21_232442_create_shop_kitchen_table.php** - `shop_kitchen`
15. **2025_12_21_232525_create_shop_driver_table.php** - `shop_driver`
16. **2025_12_21_232608_create_kitchen_driver_table.php** - `kitchen_driver`
17. **2025_12_21_232649_create_supervisor_shop_table.php** - `supervisor_shop`
18. **2025_12_25_170436_create_supervisor_kitchen_table.php** - `supervisor_kitchen`
19. **2025_12_25_170514_create_supervisor_driver_table.php** - `supervisor_driver`

Raison: Relations many-to-many entre users et entités business - utilisées dans système multi-panel

---

### **Système Permissions Core (2 migrations)**

20. **2025_12_21_125142_create_permissions_table.php**
   - Table: `permissions`
   - Raison: Table core permissions - utilisée par nouveau système

21. **2025_12_21_125143_create_permission_groups_table.php**
   - Table: `permission_groups`
   - Raison: Groupement permissions - améliorée avec hiérarchie dans nouveau système

---

### **User Groups (3 migrations) - GARDÉES et AMÉLIORÉES**

22. **2025_12_21_125144_create_user_groups_table.php**
   - Table: `user_groups`
   - Raison: Groupes users CONSERVÉS et AMÉLIORÉS dans nouveau système (hiérarchie + templates)

23. **2025_12_21_125145_create_user_group_permissions_table.php**
   - Table: `user_group_permissions`
   - Raison: Permissions de groupes - toujours utilisée

24. **2025_12_21_125145_create_user_group_members_table.php**
   - Table: `user_group_members`
   - Raison: Membres de groupes - améliorée avec scopes dans nouveau système

---

### **Configuration Panels (1 migration)**

25. **2025_12_21_232210_create_panel_configurations_table.php**
   - Table: `panel_configurations`
   - Raison: Configuration UI Filament panels

---

### **Nouveau Système Autorisation (15 migrations)**

26. **2025_12_26_000001_create_scopes_table.php**
   - Table: `scopes`
   - Raison: Système unifié de scopes polymorphiques

27. **2025_12_26_000002_create_permission_templates_table.php**
   - Table: `permission_templates`
   - Raison: Templates de permissions hiérarchiques (remplace roles)

28. **2025_12_26_000003_create_permission_wildcards_table.php**
   - Table: `permission_wildcards`
   - Raison: Patterns wildcard avec auto-expansion

29. **2025_12_26_000004_create_wildcard_pivots_tables.php**
   - Tables: `wildcard_permissions`, `template_wildcards`
   - Raison: Relations wildcards-permissions et templates-wildcards

30. **2025_12_26_000005_create_template_permissions_table.php**
   - Table: `template_permissions`
   - Raison: Permissions des templates (remplace role_permissions)

31. **2025_12_26_000006_create_user_templates_table.php**
   - Table: `user_templates`
   - Raison: Assignation templates aux users (remplace user_roles)

32. **2025_12_26_000007_create_permission_template_hierarchy_table.php**
   - Table: `permission_template_hierarchy`
   - Raison: Hiérarchie templates avec closure table

33. **2025_12_26_000008_create_user_group_hierarchy_table.php**
   - Table: `user_group_hierarchy`
   - Raison: Hiérarchie user groups avec closure table

34. **2025_12_26_000009_create_permission_group_hierarchy_table.php**
   - Table: `permission_group_hierarchy`
   - Raison: Hiérarchie permission groups avec closure table

35. **2025_12_26_000010_create_permission_audit_log_table.php**
   - Table: `permission_audit_log`
   - Raison: Audit trail complet avec IP, user agent, metadata

36. **2025_12_26_000011_create_permission_rate_limits_table.php**
   - Table: `permission_rate_limits`
   - Raison: Rate limiting des permission checks

37. **2025_12_26_000012_create_permission_delegations_table.php**
   - Table: `permission_delegations`
   - Raison: Délégations temporaires de permissions

38. **2025_12_26_000013_create_delegation_chain_table.php**
   - Table: `delegation_chain`
   - Raison: Chaîne de re-délégations

39. **2025_12_26_000014_create_permission_template_versions_table.php**
   - Table: `permission_template_versions`
   - Raison: Versioning des templates avec snapshots JSON

40. **2025_12_26_000015_create_permission_requests_table.php**
   - Table: `permission_requests`
   - Raison: Workflow d'approbation de permissions

---

### **Améliorations Nouveau Système (4 migrations)**

41. **2025_12_26_000016_add_hierarchy_to_user_groups_table.php**
   - Table: `user_groups` (ALTER)
   - Ajoute: `parent_id`, `level`, `template_id`, `auto_sync_template`
   - Raison: Améliore user_groups avec hiérarchie et intégration templates

42. **2025_12_26_000017_add_hierarchy_to_permission_groups_table.php**
   - Table: `permission_groups` (ALTER)
   - Ajoute: `parent_id`, `level`
   - Raison: Ajoute support hiérarchie aux permission groups

43. **2025_12_26_000019_add_scope_to_user_group_members_table.php**
   - Table: `user_group_members` (ALTER)
   - Ajoute: `scope_id` (FK vers scopes)
   - Raison: Ajoute support scopes unifiés aux membres de groupes

44. **2025_12_26_000020_add_primary_template_to_users_table.php**
   - Table: `users` (ALTER)
   - Ajoute: `primary_template_id`
   - Raison: Remplace `primary_role_id` par `primary_template_id`

---

## ❌ MIGRATIONS À SUPPRIMER (14 fichiers)

### **Ancien Système RBAC (5 migrations)**

1. **2025_12_21_125132_create_roles_table.php**
   - Table: `roles`
   - Remplacée par: `permission_templates`
   - Raison: Table obsolète - sera droppée par cleanup migration
   - Sécurité: ✅ SAFE - données migrées vers permission_templates

2. **2025_12_21_125143_create_role_permissions_table.php**
   - Table: `role_permissions`
   - Remplacée par: `template_permissions`
   - Raison: Pivot obsolète - sera droppée par cleanup migration
   - Sécurité: ✅ SAFE - données migrées vers template_permissions

3. **2025_12_21_125144_create_user_roles_table.php**
   - Table: `user_roles`
   - Remplacée par: `user_templates`
   - Raison: Assignation users-roles obsolète - sera droppée par cleanup migration
   - Sécurité: ✅ SAFE - données migrées vers user_templates

4. **2025_12_21_140002_create_role_hierarchy_table.php**
   - Table: `role_hierarchy`
   - Remplacée par: `permission_template_hierarchy`
   - Raison: Hiérarchie roles obsolète - sera droppée par cleanup migration
   - Sécurité: ✅ SAFE - données migrées vers closure table

5. **2025_12_21_232134_create_default_permission_templates_table.php**
   - Table: `default_permission_templates`
   - Remplacée par: `permission_templates` (unifiée)
   - Raison: Ancien système templates séparé - sera droppée par cleanup migration
   - Sécurité: ✅ SAFE - données migrées vers permission_templates

---

### **Anciennes Améliorations RBAC (3 migrations)**

6. **2025_12_21_125613_add_primary_role_to_users_table.php**
   - Table: `users` (ALTER)
   - Ajoute: `primary_role_id`
   - Remplacée par: Migration 000020 qui ajoute `primary_template_id`
   - Raison: Colonne obsolète - sera droppée par cleanup migration
   - Sécurité: ✅ SAFE - remplacée par primary_template_id

7. **2025_12_21_140000_add_scope_and_validity_to_user_roles_table.php**
   - Table: `user_roles` (ALTER)
   - Ajoute: `scope_type`, `scope_id`, validity fields
   - Raison: Table entière user_roles obsolète
   - Sécurité: ✅ SAFE - table sera droppée

8. **2025_12_21_140001_create_user_permissions_table.php**
   - Table: `user_permissions`
   - Problème: Crée table avec ANCIENNES colonnes `scope_type`, `scope_id` (polymorphic)
   - Remplacée par: Migration 000018 qui ajoute NOUVEAU `scope_id` (FK vers scopes)
   - Raison: Version obsolète avec ancien système de scopes
   - Sécurité: ⚠️ ATTENTION - vérifier que cleanup migration supprime anciennes colonnes scope

---

### **Anciens Pivots Templates (1 migration)**

9. **2025_12_25_170551_create_template_pivots_tables.php**
   - Tables: `template_roles`, `template_permissions` (ancienne version), `template_user_groups`
   - Remplacée par: Migrations 000004, 000005 du nouveau système
   - Raison: Pivots pour ancien système default_permission_templates
   - Sécurité: ✅ SAFE - tables seront droppées par cleanup migration

---

### **Migrations de Données (6 migrations) - À SUPPRIMER APRÈS MIGRATION**

Ces migrations sont des scripts one-time pour migrer les données de l'ancien au nouveau système. Elles doivent être **supprimées APRÈS que la migration soit réussie** pour éviter de re-exécuter lors de futurs migrate:fresh.

10. **2025_12_26_100001_create_scopes_from_existing_data.php**
    - Type: DATA MIGRATION
    - Action: Crée scopes depuis entités existantes (shops, kitchens, etc.)
    - Raison: One-time - supprimer après migration réussie

11. **2025_12_26_100002_migrate_roles_to_templates.php**
    - Type: DATA MIGRATION
    - Action: Copie roles → permission_templates
    - Raison: One-time - supprimer après migration réussie

12. **2025_12_26_100003_migrate_default_templates_to_templates.php**
    - Type: DATA MIGRATION
    - Action: Copie default_permission_templates → permission_templates
    - Raison: One-time - supprimer après migration réussie

13. **2025_12_26_100004_migrate_role_permissions_to_template_permissions.php**
    - Type: DATA MIGRATION
    - Action: Copie role_permissions → template_permissions
    - Raison: One-time - supprimer après migration réussie

14. **2025_12_26_100005_migrate_user_roles_to_user_templates.php**
    - Type: DATA MIGRATION
    - Action: Copie user_roles → user_templates
    - Raison: One-time - supprimer après migration réussie

15. **2025_12_26_100006_rebuild_all_hierarchies.php**
    - Type: DATA MIGRATION
    - Action: Rebuild toutes les closure tables de hiérarchie
    - Raison: One-time - supprimer après migration réussie

---

## ⚠️ MIGRATIONS À VÉRIFIER (3 fichiers)

### **1. Migration user_permissions avec conflits scope**

**2025_12_21_140001_create_user_permissions_table.php**

**Problème** : Cette migration crée la table `user_permissions` avec l'ANCIEN système de scopes polymorphiques :
- `scope_type` (string)
- `scope_id` (integer)

Alors que la migration **2025_12_26_000018_add_scope_and_conditions_to_user_permissions_table.php** ajoute le NOUVEAU système :
- `scope_id` (FK vers table scopes)
- `conditions` (JSON)
- `source`, `source_id`

**Conflit** : Deux colonnes `scope_id` avec significations différentes !

**Vérification requise** :
1. La migration 000018 doit RENOMMER ou DROPPER les anciennes colonnes avant d'ajouter les nouvelles
2. La cleanup migration (200001) doit supprimer `scope_type` et ancien `scope_id`

**Recommandation** : **GARDER** mais vérifier que migration 000018 gère correctement la transition

---

### **2. Migration d'ajout nouveau scope_id**

**2025_12_26_000018_add_scope_and_conditions_to_user_permissions_table.php**

**Table** : `user_permissions` (ALTER)

**Ajoute** :
- `scope_id` (FK vers scopes) - NOUVEAU système unifié
- `conditions` (JSON) - Conditions contextuelles
- `source` (enum) - Source de la permission
- `source_id` (integer) - ID source

**Vérification requise** :
- Confirmer que cette migration drop/rename les anciennes colonnes scope AVANT d'ajouter les nouvelles
- Ou confirmer que cleanup migration (200001) le fait

**Recommandation** : **GARDER** - migration essentielle pour nouveau système

---

### **3. Cleanup Migration - CRITIQUE**

**2025_12_26_200001_cleanup_old_permission_system_tables.php**

**Type** : CLEANUP MIGRATION

**Actions** :
- Drop tables: `roles`, `role_permissions`, `role_hierarchy`, `user_roles`, `default_permission_templates`, `template_roles`
- Drop colonnes: `users.primary_role_id`, `user_permissions.scope_type`, `user_group_members.scope_type`

**⚠️ ATTENTION - Vérifications OBLIGATOIRES** :

1. **Timing** :
   - ❌ NE PAS EXÉCUTER avant que toutes les migrations de données (100001-100006) soient réussies
   - ❌ NE PAS EXÉCUTER si des rollbacks sont possibles
   - ✅ EXÉCUTER seulement en production après validation complète

2. **Backup** :
   - ✅ Backup complet database AVANT exécution
   - ✅ Vérifier que données migrées correctement (comparer counts)
   - ✅ Tester rollback sur environnement staging

3. **Foreign Keys** :
   - ✅ Vérifier qu'aucune FK externe ne pointe vers tables à dropper
   - ✅ Vérifier que Code n'utilise plus Model Role, DefaultPermissionTemplate

**Recommandation** : **GARDER** mais exécuter avec EXTRÊME PRUDENCE après validation complète

---

## 🚨 DÉCOUVERTES CRITIQUES

### **1. Models Obsolètes Toujours Présents**

**Fichiers détectés** :
- `app/Models/Role.php` - ✅ EXISTE
- `app/Models/DefaultPermissionTemplate.php` - ✅ EXISTE

**Utilisations trouvées** :
- `Role` utilisé dans : `app/Http/Controllers/Auth/RegisterController.php`
- `DefaultPermissionTemplate` : AUCUNE utilisation trouvée

**Relation primaryRole dans User** :
- Définie dans `app/Models/User.php`
- Utilisée dans méthodes `hasRole()`, `hasAnyRole()`, `getRoleSlugs()`

**⚠️ ACTION REQUISE** :
1. Modifier `RegisterController` pour utiliser `PermissionTemplate` au lieu de `Role`
2. Mettre à jour `User` model :
   - Remplacer `primaryRole()` par `primaryTemplate()`
   - Mettre à jour `hasRole()` → `hasTemplate()`
   - Mettre à jour `getRoleSlugs()` → `getTemplateSlugs()`
3. Supprimer Models obsolètes APRÈS mise à jour code
4. Supprimer Filament Resources obsolètes (RoleResource déjà supprimé ✅)

---

### **2. User Groups CONSERVÉS et Améliorés**

**Contrairement à Roles**, les User Groups sont **GARDÉS** et **AMÉLIORÉS** :

**Améliorations apportées** :
- Hiérarchie avec `parent_id`, `level` (migration 000016)
- Intégration templates avec `template_id`, `auto_sync_template`
- Support scopes unifiés dans `user_group_members` (migration 000019)
- Closure table pour hiérarchie (migration 000008)

**Tables conservées** :
- `user_groups` (améliorée)
- `user_group_members` (améliorée avec scope_id)
- `user_group_permissions` (conservée)
- `user_group_hierarchy` (nouvelle closure table)

---

### **3. Séquence Migration de Données**

**Ordre d'exécution OBLIGATOIRE** :

```
1. CREATE nouveau système (000001-000020)
2. MIGRATE données (100001-100006) :
   a. 100001: Créer scopes depuis entités
   b. 100002: Roles → Templates
   c. 100003: Default Templates → Templates
   d. 100004: Role Permissions → Template Permissions
   e. 100005: User Roles → User Templates
   f. 100006: Rebuild hiérarchies
3. CLEANUP ancien système (200001)
4. SUPPRIMER migrations one-time (100001-100006)
```

---

## 📋 PLAN D'ACTION RECOMMANDÉ

### **PHASE 1 : Préparation (AVANT suppression)**

1. ✅ **Backup complet database**
   ```bash
   php artisan db:backup
   ```

2. ✅ **Vérifier migrations exécutées**
   ```bash
   php artisan migrate:status
   ```

3. ✅ **Compter données avant migration**
   ```sql
   SELECT COUNT(*) FROM roles;
   SELECT COUNT(*) FROM permission_templates;
   SELECT COUNT(*) FROM user_roles;
   SELECT COUNT(*) FROM user_templates;
   ```

---

### **PHASE 2 : Mise à jour Code (AVANT cleanup migration)**

1. **Modifier RegisterController**
   - Remplacer `Role` par `PermissionTemplate`

2. **Modifier User Model**
   - Remplacer `primaryRole()` par `primaryTemplate()`
   - Mettre à jour méthodes `hasRole()` etc.

3. **Supprimer Filament Resources obsolètes** (déjà fait ✅)
   - RoleResource
   - DefaultPermissionTemplateResource

---

### **PHASE 3 : Exécution Cleanup (PRODUCTION)**

1. **Exécuter cleanup migration**
   ```bash
   php artisan migrate --path=database/migrations/2025_12_26_200001_cleanup_old_permission_system_tables.php
   ```

2. **Vérifier résultats**
   ```sql
   -- Ces requêtes doivent échouer (tables n'existent plus)
   SELECT * FROM roles LIMIT 1;
   SELECT * FROM user_roles LIMIT 1;

   -- Ces requêtes doivent réussir
   SELECT * FROM permission_templates LIMIT 1;
   SELECT * FROM user_templates LIMIT 1;
   ```

3. **Vérifier colonnes supprimées**
   ```sql
   SHOW COLUMNS FROM users LIKE 'primary_role_id'; -- Doit être vide
   SHOW COLUMNS FROM users LIKE 'primary_template_id'; -- Doit exister
   ```

---

### **PHASE 4 : Nettoyage Fichiers (APRÈS cleanup réussi)**

1. **Supprimer Models obsolètes**
   ```bash
   rm app/Models/Role.php
   rm app/Models/DefaultPermissionTemplate.php
   ```

2. **Supprimer migrations obsolètes**
   Exécuter le script de suppression ci-dessous

---

## 📝 RÉSUMÉ DÉCISIONS

| Catégorie | Action | Nombre | Timing |
|-----------|--------|--------|--------|
| Laravel Core | **GARDER** | 3 | - |
| Business Entities | **GARDER** | 16 | - |
| Permission Core | **GARDER** | 2 | - |
| User Groups | **GARDER** (améliorés) | 3 | - |
| Nouveau Système | **GARDER** | 19 | - |
| Ancien RBAC | **SUPPRIMER** | 8 | Après cleanup migration |
| Data Migrations | **SUPPRIMER** | 6 | Après exécution réussie |
| Cleanup Migration | **GARDER** | 1 | Exécuter en dernier avec backup |

**Total migrations finales** : 44 (après suppression de 17 migrations obsolètes)

---

## ⚡ PROCHAINES ÉTAPES

1. ✅ Lire ce rapport complètement
2. ✅ Vérifier que migrations de données (100001-100006) ont réussi
3. ✅ Mettre à jour code (RegisterController, User model)
4. ✅ Créer backup complet database
5. ✅ Exécuter cleanup migration (200001) en staging
6. ✅ Valider que tout fonctionne
7. ✅ Exécuter cleanup migration en production
8. ✅ Supprimer fichiers migration obsolètes avec script ci-dessous
9. ✅ Supprimer Models obsolètes
10. ✅ Commit changes

---

**Rapport généré par** : Claude Code Agent
**Date** : 2025-12-27
**Version** : 1.0.0
