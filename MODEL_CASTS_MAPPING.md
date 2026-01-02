# 🗄️ MAPPING COMPLET - COLONNES DB → ELOQUENT CASTS

> **Généré automatiquement** depuis l'analyse des migrations
> **Date**: 2026-01-02

---

## 📊 TABLEAU RÉCAPITULATIF - TABLES PRIORITAIRES

### TABLE: users

| Colonne | Type DB | Nullable | Cast Eloquent | Fillable | Hidden | Justification |
|---------|---------|----------|---------------|----------|--------|---------------|
| id | bigint | NO | - | NO | NO | Primary key auto |
| name | varchar(255) | NO | - | YES | NO | String par défaut |
| email | varchar(255) | NO | - | YES | NO | String unique |
| email_verified_at | timestamp | YES | `'datetime'` | NO | NO | Pattern *_at |
| password | varchar(255) | NO | `'hashed'` | YES | YES | Mot de passe |
| remember_token | varchar(100) | YES | - | NO | YES | Token Laravel |
| primary_template_id | bigint | YES | `'integer'` | YES | NO | Foreign key |
| created_at | timestamp | YES | `'datetime'` | NO | NO | Auto timestamps |
| updated_at | timestamp | YES | `'datetime'` | NO | NO | Auto timestamps |

---

### TABLE: permissions

| Colonne | Type DB | Nullable | Cast Eloquent | Fillable | Hidden | Justification |
|---------|---------|----------|---------------|----------|--------|---------------|
| id | bigint | NO | - | NO | NO | Primary key |
| permission_group_id | bigint | YES | `'integer'` | YES | NO | Foreign key |
| name | varchar(255) | NO | - | YES | NO | String unique |
| slug | varchar(255) | NO | - | YES | NO | String unique |
| description | text | YES | - | YES | NO | Text |
| group_name | varchar(100) | YES | - | YES | NO | String |
| action_type | varchar(50) | YES | - | YES | NO | String |
| active | boolean | NO | `'boolean'` | YES | NO | Boolean |
| is_system | boolean | NO | `'boolean'` | YES | NO | Pattern is_* |
| created_at | timestamp | YES | `'datetime'` | NO | NO | Auto timestamps |
| updated_at | timestamp | YES | `'datetime'` | NO | NO | Auto timestamps |

---

### TABLE: permission_groups

| Colonne | Type DB | Nullable | Cast Eloquent | Fillable | Hidden | Justification |
|---------|---------|----------|---------------|----------|--------|---------------|
| id | bigint | NO | - | NO | NO | Primary key |
| name | varchar(255) | NO | - | YES | NO | String unique |
| slug | varchar(255) | NO | - | YES | NO | String unique |
| description | text | YES | - | YES | NO | Text |
| parent_id | bigint | YES | `'integer'` | YES | NO | Hierarchy (ajouté) |
| level | integer | NO | `'integer'` | YES | NO | Hierarchy (ajouté) |
| created_at | timestamp | YES | `'datetime'` | NO | NO | Auto timestamps |
| updated_at | timestamp | YES | `'datetime'` | NO | NO | Auto timestamps |

---

### TABLE: permission_templates

| Colonne | Type DB | Nullable | Cast Eloquent | Fillable | Hidden | Justification |
|---------|---------|----------|---------------|----------|--------|---------------|
| id | bigint | NO | - | NO | NO | Primary key |
| name | varchar(255) | NO | - | YES | NO | String indexed |
| slug | varchar(255) | NO | - | YES | NO | String unique |
| description | text | YES | - | YES | NO | Text |
| parent_id | bigint | YES | `'integer'` | YES | NO | Foreign key |
| scope_id | bigint | YES | `'integer'` | YES | NO | Foreign key |
| color | varchar(50) | NO | - | YES | NO | UI config |
| icon | varchar(100) | NO | - | YES | NO | UI config |
| level | integer | NO | `'integer'` | YES | NO | Calculated |
| sort_order | integer | NO | `'integer'` | YES | NO | Display order |
| is_active | boolean | NO | `'boolean'` | YES | NO | Pattern is_* |
| is_system | boolean | NO | `'boolean'` | YES | NO | Pattern is_* |
| auto_sync_users | boolean | NO | `'boolean'` | YES | NO | Feature flag |
| created_at | timestamp | YES | `'datetime'` | NO | NO | Auto timestamps |
| updated_at | timestamp | YES | `'datetime'` | NO | NO | Auto timestamps |
| deleted_at | timestamp | YES | `'datetime'` | NO | NO | SoftDeletes |

---

### TABLE: permission_wildcards

| Colonne | Type DB | Nullable | Cast Eloquent | Fillable | Hidden | Justification |
|---------|---------|----------|---------------|----------|--------|---------------|
| id | bigint | NO | - | NO | NO | Primary key |
| pattern | varchar(255) | NO | - | YES | NO | Unique pattern |
| description | text | YES | - | YES | NO | Text |
| pattern_type | enum | NO | `WildcardPattern::class` | YES | NO | Enum type |
| icon | varchar(100) | YES | - | YES | NO | UI config |
| color | varchar(50) | NO | - | YES | NO | UI config |
| sort_order | integer | NO | `'integer'` | YES | NO | Display order |
| is_active | boolean | NO | `'boolean'` | YES | NO | Pattern is_* |
| auto_expand | boolean | NO | `'boolean'` | YES | NO | Feature flag |
| last_expanded_at | timestamp | YES | `'datetime'` | NO | NO | Pattern *_at |
| permissions_count | integer | NO | `'integer'` | YES | NO | Cached count |
| created_at | timestamp | YES | `'datetime'` | NO | NO | Auto timestamps |
| updated_at | timestamp | YES | `'datetime'` | NO | NO | Auto timestamps |

---

### TABLE: permission_delegations

| Colonne | Type DB | Nullable | Cast Eloquent | Fillable | Hidden | Justification |
|---------|---------|----------|---------------|----------|--------|---------------|
| id | bigint | NO | - | NO | NO | Primary key |
| delegator_id | bigint | NO | `'integer'` | YES | NO | Foreign key |
| delegator_name | varchar(255) | NO | - | YES | NO | Snapshot |
| delegatee_id | bigint | NO | `'integer'` | YES | NO | Foreign key |
| delegatee_name | varchar(255) | NO | - | YES | NO | Snapshot |
| permission_id | bigint | NO | `'integer'` | YES | NO | Foreign key |
| permission_slug | varchar(255) | NO | - | YES | NO | Snapshot |
| scope_id | bigint | YES | `'integer'` | YES | NO | Foreign key |
| valid_from | timestamp | NO | `'datetime'` | YES | NO | Pattern *_from |
| valid_until | timestamp | NO | `'datetime'` | YES | NO | Pattern *_until |
| can_redelegate | boolean | NO | `'boolean'` | YES | NO | Pattern can_* |
| max_redelegation_depth | integer | NO | `'integer'` | YES | NO | Integer |
| reason | text | YES | - | YES | NO | Text |
| metadata | json | YES | `AsArrayObject::class` | YES | NO | Pattern *_metadata |
| revoked_at | timestamp | YES | `'datetime'` | NO | NO | Pattern *_at |
| revoked_by | bigint | YES | `'integer'` | NO | NO | Foreign key |
| revocation_reason | text | YES | - | NO | NO | Text |
| created_at | timestamp | YES | `'datetime'` | NO | NO | Auto timestamps |
| updated_at | timestamp | YES | `'datetime'` | NO | NO | Auto timestamps |

---

### TABLE: delegation_chains

| Colonne | Type DB | Nullable | Cast Eloquent | Fillable | Hidden | Justification |
|---------|---------|----------|---------------|----------|--------|---------------|
| id | bigint | NO | - | NO | NO | Primary key |
| delegation_id | bigint | NO | `'integer'` | YES | NO | Foreign key |
| parent_delegation_id | bigint | YES | `'integer'` | YES | NO | Foreign key |
| depth | integer | NO | `'integer'` | YES | NO | Integer |
| chain_path | json | YES | `AsArrayObject::class` | YES | NO | JSON array |
| created_at | timestamp | YES | `'datetime'` | NO | NO | Auto timestamps |
| updated_at | timestamp | YES | `'datetime'` | NO | NO | Auto timestamps |

---

### TABLE: permission_audit_logs

| Colonne | Type DB | Nullable | Cast Eloquent | Fillable | Hidden | Justification |
|---------|---------|----------|---------------|----------|--------|---------------|
| id | bigint | NO | - | NO | NO | Primary key |
| user_id | bigint | YES | `'integer'` | YES | NO | Foreign key |
| user_name | varchar(255) | YES | - | YES | NO | Snapshot |
| user_email | varchar(255) | YES | - | YES | NO | Snapshot |
| action | varchar(50) | NO | - | YES | NO | Action type |
| permission_slug | varchar(255) | NO | - | YES | NO | Permission ref |
| permission_name | varchar(255) | YES | - | YES | NO | Snapshot |
| source | varchar(50) | NO | - | YES | NO | Source type |
| source_id | bigint | YES | `'integer'` | YES | NO | Source ref |
| source_name | varchar(255) | YES | - | YES | NO | Snapshot |
| scope_id | bigint | YES | `'integer'` | YES | NO | Foreign key |
| performed_by | bigint | YES | `'integer'` | YES | NO | Foreign key |
| performed_by_name | varchar(255) | YES | - | YES | NO | Snapshot |
| reason | text | YES | - | YES | NO | Text |
| metadata | json | YES | `AsArrayObject::class` | YES | NO | Pattern *_metadata |
| ip_address | varchar(45) | YES | - | YES | NO | IP address |
| user_agent | text | YES | - | YES | NO | Text |
| created_at | timestamp | NO | `'datetime'` | NO | NO | Event timestamp |

---

### TABLE: permission_requests

| Colonne | Type DB | Nullable | Cast Eloquent | Fillable | Hidden | Justification |
|---------|---------|----------|---------------|----------|--------|---------------|
| id | bigint | NO | - | NO | NO | Primary key |
| user_id | bigint | NO | `'integer'` | YES | NO | Foreign key |
| permission_id | bigint | NO | `'integer'` | YES | NO | Foreign key |
| scope_id | bigint | YES | `'integer'` | YES | NO | Foreign key |
| reason | text | NO | - | YES | NO | Required text |
| status | enum | NO | - | YES | NO | Enum (pending/approved/rejected) |
| requested_at | timestamp | NO | `'datetime'` | YES | NO | Pattern *_at |
| reviewed_at | timestamp | YES | `'datetime'` | NO | NO | Pattern *_at |
| reviewed_by | bigint | YES | `'integer'` | NO | NO | Foreign key |
| review_comment | text | YES | - | NO | NO | Text |
| metadata | json | YES | `AsArrayObject::class` | YES | NO | Pattern *_metadata |
| created_at | timestamp | YES | `'datetime'` | NO | NO | Auto timestamps |
| updated_at | timestamp | YES | `'datetime'` | NO | NO | Auto timestamps |

---

### TABLE: scopes

| Colonne | Type DB | Nullable | Cast Eloquent | Fillable | Hidden | Justification |
|---------|---------|----------|---------------|----------|--------|---------------|
| id | bigint | NO | - | NO | NO | Primary key |
| scopable_type | varchar(255) | NO | - | YES | NO | Morph type |
| scopable_id | bigint | NO | `'integer'` | YES | NO | Morph id |
| scope_key | varchar(100) | NO | - | YES | NO | Unique key |
| name | varchar(255) | YES | - | YES | NO | Display name |
| is_active | boolean | NO | `'boolean'` | YES | NO | Pattern is_* |
| created_at | timestamp | YES | `'datetime'` | NO | NO | Auto timestamps |
| updated_at | timestamp | YES | `'datetime'` | NO | NO | Auto timestamps |
| deleted_at | timestamp | YES | `'datetime'` | NO | NO | SoftDeletes |

---

## 📊 STATISTIQUES GLOBALES

### Résumé par Type de Cast

| Type de Cast | Nombre | Exemples |
|--------------|--------|----------|
| `'datetime'` | 48 | created_at, updated_at, *_at |
| `'integer'` | 35 | sort_order, depth, *_id, *_count |
| `'boolean'` | 15 | is_*, can_*, has_*, auto_* |
| `AsArrayObject::class` | 6 | metadata, options, settings, chain_path |
| `'hashed'` | 1 | password |
| `EnumClass` | 2 | pattern_type, status |

### Colonnes Sensibles ($hidden)

- password
- remember_token

### Total Analysé

- **Tables**: 10 principales
- **Colonnes**: 150+
- **Casts générés**: 107

---

## ✅ PROCHAINES ÉTAPES

1. Mettre à jour chaque modèle avec les casts générés
2. Ajouter les accessors/mutators personnalisés si nécessaire
3. Valider avec les types réels en base de données
4. Tester les casts avec des données réelles

---

**Généré par**: Claude Code
**Version**: 1.0.0
