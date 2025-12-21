# 🚀 Guide de Démarrage Rapide - Noflaye Box

## ✅ Prérequis Vérifiés

Votre projet est configuré avec:
- ✅ Laravel 12
- ✅ Filament v4 (5 panels: Admin, Shop, Kitchen, Driver, Supplier)
- ✅ Inertia v2 + React + TypeScript
- ✅ Système d'authentification & autorisation complet

---

## 🔧 Étape 1: Migrer la Base de Données

```bash
# Supprimer et recréer la base avec les données de test
php artisan migrate:fresh --seed
```

**Résultat attendu:**
- ✅ 17 rôles créés
- ✅ 42 permissions créées
- ✅ Permissions attribuées aux rôles
- ✅ 4 utilisateurs de test créés
- ✅ 2 boutiques créées
- ✅ 1 fournisseur créé

---

## 🎨 Étape 2: Compiler les Assets Frontend

```bash
# Pour le développement (avec hot reload)
npm run dev

# OU pour la production
npm run build
```

---

## 🌐 Étape 3: Démarrer le Serveur

```bash
php artisan serve
```

Le serveur démarrera sur: `http://localhost:8000`

---

## 👥 Étape 4: Tester les Comptes

### Comptes de Test Créés

| Email | Mot de passe | Rôle | Accès Panel |
|-------|--------------|------|-------------|
| admin@noflaye.sn | password | Super Admin | Admin |
| shop@noflaye.sn | password | Shop Manager | Shop, Kitchen |
| supplier@noflaye.sn | password | Supplier Manager | Supplier |
| driver@noflaye.sn | password | Driver | Driver |

### Se connecter

1. **Panel Admin**
   ```
   URL: http://localhost:8000/admin
   Email: admin@noflaye.sn
   Password: password
   ```

2. **Panel Shop**
   ```
   URL: http://localhost:8000/shop
   Email: shop@noflaye.sn
   Password: password
   ```

3. **Panel Supplier**
   ```
   URL: http://localhost:8000/supplier
   Email: supplier@noflaye.sn
   Password: password
   ```

4. **Panel Driver**
   ```
   URL: http://localhost:8000/driver
   Email: driver@noflaye.sn
   Password: password
   ```

---

## 📝 Étape 5: S'inscrire en tant que Client

```
URL: http://localhost:8000/register
```

Le formulaire d'inscription est disponible avec:
- ✅ Validation temps réel
- ✅ Toggle visibilité password
- ✅ Design moderne Tailwind CSS
- ✅ Rôle "Customer" attribué automatiquement
- ✅ Connexion automatique après inscription

---

## 🔍 Étape 6: Vérifier les Fonctionnalités

### Panel Switching
- Connectez-vous avec `admin@noflaye.sn`
- Vérifiez que vous voyez tous les panels dans la navigation
- Essayez de naviguer entre les différents panels

### Multi-Tenancy
- Connectez-vous avec `shop@noflaye.sn`
- Vous devriez voir 2 boutiques: "Yassa House" et "Thiebou Délice"
- Testez le changement de boutique dans Filament

### Permissions
Testez dans Tinker:
```bash
php artisan tinker
```

```php
// Charger un utilisateur
$user = User::where('email', 'shop@noflaye.sn')->first();

// Vérifier les rôles
$user->getRoleSlugs();
// => ["shop_manager"]

// Vérifier une permission
$user->hasPermission('orders.create');
// => true

$user->hasPermission('settings.permissions.manage');
// => false (seul super_admin peut)

// Vérifier les boutiques gérées
$user->getManagedShops();
// => Collection de 2 shops

// Vérifier si gère une boutique spécifique
$user->managesShop(1);
// => true

// Récupérer les panels accessibles
$user->getAccessiblePanels();
// => array avec shop, kitchen panels
```

---

## 🐛 Debugging & Outils

### Vérifier les migrations
```bash
php artisan migrate:status
```

### Vérifier les routes
```bash
php artisan route:list
```

### Vérifier les rôles créés
```bash
php artisan tinker
Role::all()->pluck('name', 'slug')
```

### Vérifier les permissions créées
```bash
php artisan tinker
Permission::all()->pluck('name', 'slug')
```

### Nettoyer le cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Formater le code
```bash
vendor/bin/pint
```

---

## 📊 Structure des Données

### Rôles Créés (17)

**Administrateurs:**
- Super Admin (level 100)
- Admin (level 90)

**Shop Managers:**
- Shop Manager Senior (level 83)
- Shop Manager (level 82)
- Shop Manager Junior (level 81)
- Shop Manager Trainee (level 80)

**Cuisine:**
- Kitchen Manager (level 72)
- Kitchen Staff (level 70)

**Livraison:**
- Driver (level 60)

**Fournisseurs:**
- Supplier Manager (level 55)
- Supplier Staff (level 53)

**Support:**
- Support Manager (level 53)
- Support Tier 2 (level 52)
- Support Tier 1 (level 51)

**Autres:**
- Partner (level 50)
- VIP Customer (level 10)
- Customer (level 1)

### Permissions Créées (42)

Organisées en 10 groupes:
- **Orders** (6): read, create, update, cancel, refund, all.read
- **Products** (5): read, create, update, delete, pricing.update
- **Inventory** (4): read, update, restock, transfer
- **Kitchen** (3): orders.read, orders.prepare, inventory.manage
- **Deliveries** (3): read, assign, update
- **Analytics** (3): shop.read, all.read, reports.export
- **Users** (4): read, create, update, delete
- **Settings** (3): manage, roles.manage, permissions.manage
- **Shops** (3): read, create, update
- **Suppliers** (3): read, create, update

---

## 🎯 Cas d'Usage Courants

### Ajouter un nouveau Shop Manager

```php
use App\Models\User;
use App\Models\Role;
use App\Models\Shop;

// Créer l'utilisateur
$user = User::create([
    'name' => 'Nouveau Manager',
    'email' => 'nouveau@manager.com',
    'password' => Hash::make('password'),
    'primary_role_id' => Role::where('slug', 'shop_manager')->first()->id,
]);

// Attacher à une boutique
$shop = Shop::find(1);
$user->shops()->attach($shop->id);
```

### Attribuer un rôle temporaire

```php
$user = User::find(1);
$role = Role::where('slug', 'shop_manager_senior')->first();

$user->roles()->attach($role->id, [
    'scope_type' => 'shop',
    'scope_id' => 1, // Shop spécifique
    'valid_from' => now(),
    'valid_until' => now()->addMonths(3), // 3 mois
    'granted_by' => auth()->id(),
    'reason' => 'Remplacement temporaire',
]);
```

### Grant une permission exceptionnelle

```php
$user = User::find(1);
$permission = Permission::where('slug', 'orders.refund')->first();

$user->permissions()->attach($permission->id, [
    'permission_type' => 'grant',
    'scope_type' => 'shop',
    'scope_id' => 1,
    'valid_from' => now(),
    'valid_until' => now()->addDays(7), // 7 jours
    'granted_by' => auth()->id(),
    'reason' => 'Autorisation exceptionnelle pour gérer remboursements',
]);
```

### Revoke une permission

```php
$user = User::find(1);
$permission = Permission::where('slug', 'products.delete')->first();

$user->permissions()->attach($permission->id, [
    'permission_type' => 'revoke',
    'scope_type' => null, // Global
    'scope_id' => null,
    'valid_from' => now(),
    'granted_by' => auth()->id(),
    'reason' => 'Retrait suite à incident',
]);
```

---

## ❓ Problèmes Courants

### Erreur "Class not found"
```bash
composer dump-autoload
```

### Erreur de migration
```bash
# Réinitialiser complètement
php artisan migrate:fresh --seed
```

### Assets non compilés
```bash
npm install
npm run build
```

### Permission denied sur vendor/bin/pint
```bash
chmod +x vendor/bin/pint
./vendor/bin/pint
```

---

## 📚 Documentation Complète

Pour plus de détails, consultez:
- `IMPLEMENTATION_COMPLETE.md` - Documentation complète de l'implémentation
- `IMPLEMENTATION_AUTH_AUTHORIZATION.md` - Plan détaillé original
- Modèles dans `app/Models/` - Code source documenté
- Seeders dans `database/seeders/` - Exemples de configuration

---

## ✅ Checklist de Vérification

Après avoir suivi ce guide, vérifiez:

- [ ] Migrations exécutées avec succès
- [ ] Assets compilés
- [ ] Serveur démarré
- [ ] Connexion admin fonctionne
- [ ] Connexion shop manager fonctionne
- [ ] Inscription client fonctionne
- [ ] Panel switching fonctionne
- [ ] Multi-tenancy fonctionne (2 shops visibles)
- [ ] Permissions vérifiées dans Tinker

---

**Tout est prêt! Bon développement! 🎉**
