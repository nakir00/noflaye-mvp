# Implementation des Autorisations - Noflaye MVP

## Date: 2026-02-01

## Résumé

Ce document décrit l'implémentation du système d'autorisation multi-panel pour l'application Noflaye MVP utilisant Filament v4.

---

## 1. Architecture Multi-Panel

L'application dispose de **6 panels Filament** :

| Panel | URL | Description |
|-------|-----|-------------|
| Admin | `/admin` | Administration globale |
| Shop | `/shop/{tenant}` | Gestion des boutiques |
| Kitchen | `/kitchen/{tenant}` | Gestion des cuisines |
| Driver | `/driver/{tenant}` | Gestion des livraisons |
| Supplier | `/supplier/{tenant}` | Gestion des fournisseurs |
| Supervisor | `/supervisor/{tenant}` | Supervision multi-entités |

---

## 2. Contrôle d'Accès aux Panels

### Fichier modifié: `app/Models/User.php`

La méthode `canAccessPanel()` a été activée (le `return true;` qui bypassait la logique a été supprimé) :

```php
public function canAccessPanel(Panel $panel): bool
{
    $panelId = $panel->getId();

    // Super admins can access all panels
    if ($this->hasAnyTemplate(['super_admin', 'admin'])) {
        return true;
    }

    return match ($panelId) {
        'admin' => $this->hasAnyTemplate(['super_admin', 'admin']),
        'shop' => $this->hasAnyTemplate(['shop_manager', 'shop_staff']) || $this->shops()->exists(),
        'kitchen' => $this->hasAnyTemplate(['kitchen_manager', 'kitchen_staff']) || $this->kitchens()->exists(),
        'driver' => $this->hasTemplate('driver') || $this->drivers()->exists(),
        'supplier' => $this->hasAnyTemplate(['supplier_manager', 'supplier_staff']) || $this->suppliers()->exists(),
        'supervisor' => $this->hasAnyTemplate(['supervisor_manager', 'supervisor_staff']) || $this->supervisors()->exists(),
        default => false,
    };
}
```

### Matrice d'Accès aux Panels

| Panel | Templates Autorisés | Condition Alternative |
|-------|--------------------|-----------------------|
| Admin | `super_admin`, `admin` | - |
| Shop | `shop_manager`, `shop_staff` | Associé à une shop |
| Kitchen | `kitchen_manager`, `kitchen_staff` | Associé à une kitchen |
| Driver | `driver` | Associé à un driver |
| Supplier | `supplier_manager`, `supplier_staff` | Associé à un supplier |
| Supervisor | `supervisor_manager`, `supervisor_staff` | Associé à un supervisor |

---

## 3. Policies Enregistrées

### Fichier modifié: `app/Providers/AuthServiceProvider.php`

Les policies suivantes ont été enregistrées :

```php
protected $policies = [
    \App\Models\User::class => \App\Policies\UserPolicy::class,
    \App\Models\Shop::class => \App\Policies\ShopPolicy::class,
    \App\Models\Kitchen::class => \App\Policies\KitchenPolicy::class,
    \App\Models\Driver::class => \App\Policies\DriverPolicy::class,
    \App\Models\Supervisor::class => \App\Policies\SupervisorPolicy::class,
    \App\Models\Supplier::class => \App\Policies\SupplierPolicy::class,
    \App\Models\Permission::class => \App\Policies\PermissionPolicy::class,
    \App\Models\PermissionTemplate::class => \App\Policies\TemplatePolicy::class,
];
```

---

## 4. Structure des Policies

Chaque policy utilise le trait `ChecksPermissions` et l'enum `Permission` pour les vérifications type-safe.

### Exemple: DriverPolicy

```php
class DriverPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->can($user, Permission::DRIVER_VIEW_ANY);
    }

    public function view(User $user, Driver $driver): bool
    {
        if ($this->can($user, Permission::DRIVER_VIEW)) {
            return true;
        }
        return $this->can($user, Permission::DRIVER_VIEW, $driver->id);
    }

    public function create(User $user): bool
    {
        return $this->can($user, Permission::DRIVER_CREATE);
    }

    public function update(User $user, Driver $driver): bool
    {
        return $this->can($user, Permission::DRIVER_UPDATE)
            || $this->can($user, Permission::DRIVER_UPDATE, $driver->id);
    }

    public function delete(User $user, Driver $driver): bool
    {
        return $this->can($user, Permission::DRIVER_DELETE);
    }
}
```

---

## 5. Système de Templates (Rôles)

Le système utilise des **PermissionTemplates** au lieu de rôles traditionnels :

### Méthodes de vérification dans User.php

```php
// Vérifie un seul template
$user->hasTemplate('shop_manager');

// Vérifie si l'utilisateur a AU MOINS UN des templates
$user->hasAnyTemplate(['shop_manager', 'shop_staff']);

// Vérifie si l'utilisateur a TOUS les templates
$user->hasAllTemplates(['admin', 'shop_manager']);
```

### Aliases de compatibilité

```php
$user->hasRole($slug);      // alias de hasTemplate()
$user->hasAnyRole($slugs);  // alias de hasAnyTemplate()
```

---

## 6. Multi-Tenancy

### getTenants()

Retourne les entités accessibles pour chaque panel :

```php
public function getTenants(Panel $panel): Collection
{
    return match ($panel->getId()) {
        'admin' => collect([]),
        'shop' => $this->hasAnyTemplate(['super_admin', 'admin']) ? Shop::all() : $this->shops,
        'kitchen' => $this->hasAnyTemplate(['super_admin', 'admin']) ? Kitchen::all() : $this->kitchens,
        // ...
    };
}
```

### canAccessTenant()

Vérifie l'accès à un tenant spécifique :

```php
public function canAccessTenant(Model $tenant): bool
{
    if ($this->hasAnyTemplate(['super_admin', 'admin'])) {
        return true;
    }

    if ($tenant instanceof Shop) {
        return $this->managesShop($tenant->id);
    }
    // ...
}
```

---

## 7. Gate Before Hook

L'AuthServiceProvider définit un `Gate::before` qui :

1. Accorde tous les droits aux utilisateurs avec le template `admin`
2. Convertit les abilities Filament en slugs de permission
3. Vérifie les permissions via templates et permissions directes

```php
Gate::before(function (User $user, string $ability) {
    if ($user->primaryTemplate?->slug === 'admin') {
        return true;
    }
    return $this->checkUserPermission($user, $ability);
});
```

---

## 8. Fichiers Modifiés

| Fichier | Modification |
|---------|--------------|
| `app/Models/User.php` | Activation de `canAccessPanel()`, mise à jour de `getTenants()` et `canAccessTenant()` |
| `app/Providers/AuthServiceProvider.php` | Ajout de Driver, Supervisor, Supplier policies |
| `app/Filament/Pages/Auth/CustomRegister.php` | Renommage de la classe (fix conflit) |

---

## 9. Prochaines Étapes Recommandées

1. **Tests** : Créer des tests pour les policies et l'accès aux panels
2. **Seeder** : Vérifier que les templates de permission existent en base
3. **UI** : Ajouter des messages d'erreur personnalisés pour les accès refusés
4. **Audit** : Logger les tentatives d'accès refusées

---

## 10. Utilisation dans les Resources Filament

Filament observe automatiquement les policies. Les Resources utilisent :

- `viewAny()` → Cache le menu si refusé
- `view()` → Affichage d'un enregistrement
- `create()` → Création
- `update()` → Modification
- `delete()` → Suppression

Pour désactiver l'autorisation sur un Resource :

```php
protected static bool $shouldSkipAuthorization = true;
```
