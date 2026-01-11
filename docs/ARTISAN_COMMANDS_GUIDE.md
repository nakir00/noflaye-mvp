# Artisan Commands Guide - Permission System

## Overview

NoFlaye MVP inclut plusieurs commandes Artisan personnalisées pour gérer le système de permissions. Ces commandes facilitent la maintenance, la synchronisation, et la validation du système de permissions.

## Commandes Disponibles

### 1. **permissions:generate-from-enum**
Génère les enregistrements de permissions dans la base de données à partir de l'enum `Permission`.

### 2. **permissions:sync** ⭐ (Nouvelle)
Synchronise les permissions utilisateur avec leurs templates assignés.

### 3. **permissions:check-policies** ⭐ (Nouvelle)
Vérifie et valide l'enregistrement des policies et l'utilisation des permissions.

### 4. **permissions:cleanup-audit**
Nettoie les anciennes entrées du journal d'audit.

### 5. **permissions:expire**
Expire les permissions utilisateur qui ont dépassé leur date d'expiration.

### 6. **permissions:expire-delegations**
Nettoie les délégations de permissions expirées.

### 7. **permissions:rebuild-hierarchies**
Reconstruit toutes les tables de fermeture de hiérarchie.

### 8. **permissions:warm-cache**
Préchauffe le cache de permissions pour tous les utilisateurs.

---

## Commandes Nouvelles (Créées)

### permissions:sync

#### Description
Synchronise les permissions utilisateur avec leurs templates de permissions assignés. Utile après avoir modifié les permissions d'un template ou pour s'assurer que tous les utilisateurs ont les bonnes permissions.

#### Signature
```bash
php artisan permissions:sync
    {--dry-run : Run without making database changes}
    {--user= : Sync permissions for specific user ID}
    {--template= : Sync only users with specific template slug}
    {--force : Force sync even if auto-sync is disabled}
```

#### Options

**`--dry-run`**
- Exécute la commande sans faire de changements en base de données
- Affiche ce qui serait modifié
- Utile pour prévisualiser les changements

```bash
php artisan permissions:sync --dry-run
```

**`--user=ID`**
- Synchronise uniquement les permissions d'un utilisateur spécifique
- ID = l'identifiant de l'utilisateur

```bash
php artisan permissions:sync --user=123
```

**`--template=SLUG`**
- Synchronise uniquement les utilisateurs avec un template spécifique
- SLUG = le slug du template (ex: shop-manager, kitchen-staff)

```bash
php artisan permissions:sync --template=shop-manager
```

**`--force`**
- Force la synchronisation même si auto-sync est désactivé
- Normalement, seuls les templates avec `auto_sync=true` sont synchronisés

```bash
php artisan permissions:sync --force
```

#### Fonctionnalités

✅ **Synchronisation basée sur templates**
- Ajoute les permissions manquantes depuis les templates
- Supprime les permissions obsolètes (source='template')
- Préserve les permissions directes (source='direct')

✅ **Auto-sync awareness**
- Respecte le paramètre `auto_sync` du template
- Option `--force` pour ignorer ce paramètre

✅ **Statistiques détaillées**
- Nombre d'utilisateurs traités
- Permissions ajoutées
- Permissions supprimées
- Permissions ignorées

✅ **Cache invalidation**
- Invalide automatiquement le cache après synchronisation

#### Exemples d'Utilisation

**Synchroniser tous les utilisateurs:**
```bash
php artisan permissions:sync
```

**Preview des changements:**
```bash
php artisan permissions:sync --dry-run
```

**Synchroniser un utilisateur spécifique:**
```bash
php artisan permissions:sync --user=5
```

**Synchroniser tous les shop managers:**
```bash
php artisan permissions:sync --template=shop-manager
```

**Force sync sans auto-sync:**
```bash
php artisan permissions:sync --force
```

**Combinaison d'options:**
```bash
php artisan permissions:sync --user=10 --dry-run
php artisan permissions:sync --template=admin --force
```

#### Output Exemple

```
🔄 Synchronizing user permissions with templates...

Found 25 user(s) to sync

 25/25 [============================] 100%

  ✅ Synchronization completed

+---------------------+-------+
| Metric              | Count |
+---------------------+-------+
| Users Processed     | 25    |
| Permissions Added   | 47    |
| Permissions Removed | 12    |
| Permissions Skipped | 8     |
| Total Changes       | 59    |
+---------------------+-------+

Cache invalidated
```

#### Cas d'Usage

1. **Après modification d'un template:**
   ```bash
   # Un admin a ajouté des permissions au template "shop-manager"
   php artisan permissions:sync --template=shop-manager
   ```

2. **Correction des permissions:**
   ```bash
   # Vérifier d'abord ce qui va changer
   php artisan permissions:sync --dry-run

   # Appliquer si tout semble correct
   php artisan permissions:sync
   ```

3. **Onboarding d'un nouvel utilisateur:**
   ```bash
   # S'assurer que le nouvel utilisateur a toutes les permissions
   php artisan permissions:sync --user=123
   ```

4. **Scheduled Task:**
   ```php
   // Dans app/Console/Kernel.php
   $schedule->command('permissions:sync')->daily();
   ```

---

### permissions:check-policies

#### Description
Vérifie et valide la cohérence entre les policies, permissions et modèles. Identifie les problèmes de configuration et fournit des recommandations.

#### Signature
```bash
php artisan permissions:check-policies
    {--detailed : Display detailed information}
    {--fix : Suggest fixes for found issues}
```

#### Options

**`--detailed`**
- Affiche des informations détaillées sur chaque policy et modèle
- Liste toutes les policies enregistrées
- Montre tous les modèles avec/sans policies

```bash
php artisan permissions:check-policies --detailed
```

**`--fix`**
- Suggère des correctifs pour les problèmes trouvés
- Fournit des commandes/actions recommandées

```bash
php artisan permissions:check-policies --fix
```

#### Fonctionnalités

✅ **Découverte automatique**
- Découvre toutes les policies dans `app/Policies`
- Découvre tous les modèles dans `app/Models`

✅ **Vérification d'enregistrement**
- Vérifie que chaque policy est correctement enregistrée avec Gate
- Détecte les policies non enregistrées

✅ **Vérification des modèles**
- Identifie les modèles sans policy
- Suggère la création de policies manquantes

✅ **Analyse d'utilisation des permissions**
- Compte combien de permissions sont utilisées dans les policies
- Identifie les permissions non utilisées
- Aide à nettoyer les permissions obsolètes

✅ **Statistiques complètes**
- Nombre total de policies
- Policies enregistrées vs non enregistrées
- Modèles avec/sans policies
- Permissions utilisées/non utilisées
- Nombre de méthodes de policy

#### Exemples d'Utilisation

**Check standard:**
```bash
php artisan permissions:check-policies
```

**Check détaillé:**
```bash
php artisan permissions:check-policies --detailed
```

**Check avec suggestions:**
```bash
php artisan permissions:check-policies --fix
```

**Check complet:**
```bash
php artisan permissions:check-policies --detailed --fix
```

#### Output Exemple

```
🔍 Checking policies and permissions...

Checking policy registration...
Checking models have policies...
Checking permission usage...

  ✅ Policy check completed

+-------------------------+-------+
| Metric                  | Count |
+-------------------------+-------+
| Total Policies          | 8     |
| Registered Policies     | 7     |
| Unregistered Policies   | 0     |
| ---                     | ---   |
| Total Models            | 19    |
| Models with Policies    | 6     |
| Models without Policies | 9     |
| ---                     | ---   |
| Total Permissions       | 79    |
| Used in Policies        | 45    |
| Unused                  | 34    |
| Policy Methods          | 50    |
+-------------------------+-------+

Issues found:

⚠️ Cannot determine model for policy: App\Policies\TemplatePolicy
⚠️ Model has no policy: App\Models\DelegationChain
⚠️ Model has no policy: App\Models\PermissionAuditLog

💡 Suggested fixes:

→ Consider creating: App\Policies\DelegationChainPolicy
→ Consider creating: App\Policies\PermissionAuditLogPolicy
```

#### Cas d'Usage

1. **Après création de nouveaux modèles:**
   ```bash
   # Vérifier si des policies sont manquantes
   php artisan permissions:check-policies --fix
   ```

2. **Audit de sécurité:**
   ```bash
   # Vérifier que toutes les policies sont bien enregistrées
   php artisan permissions:check-policies
   ```

3. **Nettoyage des permissions:**
   ```bash
   # Identifier les permissions inutilisées
   php artisan permissions:check-policies --detailed
   ```

4. **CI/CD Pipeline:**
   ```bash
   # Dans votre pipeline, vérifier la cohérence
   php artisan permissions:check-policies || exit 1
   ```

5. **Après refactoring:**
   ```bash
   # S'assurer que tout est toujours cohérent
   php artisan permissions:check-policies --detailed --fix
   ```

---

## Commandes Existantes

### permissions:generate-from-enum

**Description:** Génère les enregistrements de permissions en base de données depuis l'enum.

**Usage:**
```bash
php artisan permissions:generate-from-enum
php artisan permissions:generate-from-enum --dry-run
php artisan permissions:generate-from-enum --group="Core Permissions"
```

### permissions:cleanup-audit

**Description:** Nettoie les anciennes entrées du journal d'audit.

**Usage:**
```bash
php artisan permissions:cleanup-audit
```

### permissions:expire

**Description:** Expire les permissions utilisateur avec date d'expiration passée.

**Usage:**
```bash
php artisan permissions:expire
```

### permissions:expire-delegations

**Description:** Nettoie les délégations de permissions expirées.

**Usage:**
```bash
php artisan permissions:expire-delegations
```

### permissions:rebuild-hierarchies

**Description:** Reconstruit les tables de fermeture de hiérarchie.

**Usage:**
```bash
php artisan permissions:rebuild-hierarchies
```

### permissions:warm-cache

**Description:** Préchauffe le cache de permissions pour tous les utilisateurs.

**Usage:**
```bash
php artisan permissions:warm-cache
```

---

## Planification avec Task Scheduler

### Configuration Recommandée

Ajoutez dans `bootstrap/app.php` ou utilisez le scheduler:

```php
use Illuminate\Console\Scheduling\Schedule;

$schedule->command('permissions:sync')->daily();
$schedule->command('permissions:expire')->hourly();
$schedule->command('permissions:expire-delegations')->daily();
$schedule->command('permissions:cleanup-audit')->weekly();
$schedule->command('permissions:warm-cache')->daily()->at('02:00');
```

### Tâches Recommandées

| Commande | Fréquence | Raison |
|----------|-----------|--------|
| `permissions:sync` | Quotidienne | Maintenir les permissions à jour avec les templates |
| `permissions:expire` | Horaire | Expirer rapidement les permissions temporaires |
| `permissions:expire-delegations` | Quotidienne | Nettoyer les délégations expirées |
| `permissions:cleanup-audit` | Hebdomadaire | Gérer la taille de la table d'audit |
| `permissions:warm-cache` | Quotidienne | Performance optimale |
| `permissions:check-policies` | Sur déploiement | Validation en CI/CD |

---

## Bonnes Pratiques

### 1. Utiliser --dry-run Avant --force

❌ **Éviter:**
```bash
php artisan permissions:sync --force
```

✅ **Préférer:**
```bash
# D'abord vérifier
php artisan permissions:sync --dry-run

# Puis appliquer si OK
php artisan permissions:sync
```

### 2. Vérifier les Policies Régulièrement

```bash
# Dans votre workflow CI/CD
php artisan permissions:check-policies || exit 1
```

### 3. Synchroniser Après Modifications de Templates

```bash
# Après avoir modifié les permissions d'un template
php artisan permissions:sync --template=shop-manager --dry-run
php artisan permissions:sync --template=shop-manager
```

### 4. Surveiller les Permissions Non Utilisées

```bash
# Mensuel - vérifier et nettoyer
php artisan permissions:check-policies --detailed > permissions-audit.txt
```

---

## Dépannage

### Problème: Sync ne fait rien

**Cause:** Auto-sync désactivé sur les templates

**Solution:**
```bash
php artisan permissions:sync --force
```

### Problème: Policy non trouvée

**Cause:** Convention de nommage incorrecte

**Solution:**
```bash
# Vérifier avec
php artisan permissions:check-policies --detailed

# Renommer la policy selon: {Model}Policy
# Exemple: Shop → ShopPolicy
```

### Problème: Permissions non utilisées

**Cause:** Permissions définies dans l'enum mais non utilisées dans les policies

**Solution:**
```bash
# Identifier
php artisan permissions:check-policies --detailed

# Soit:
# 1. Ajouter dans les policies
# 2. Supprimer de l'enum si obsolète
```

---

## Récapitulatif

✅ **2 Nouvelles commandes créées:**
- `permissions:sync` - Synchronisation permissions/templates
- `permissions:check-policies` - Validation policies/permissions

✅ **Fonctionnalités:**
- Dry-run mode pour sécurité
- Statistiques détaillées
- Suggestions de correctifs
- Cache invalidation
- Support filters (user, template)

✅ **Documentation complète:**
- Signatures et options
- Exemples d'utilisation
- Cas d'usage réels
- Configuration scheduler
- Bonnes pratiques
- Dépannage

Les commandes sont prêtes à être utilisées! 🎉
