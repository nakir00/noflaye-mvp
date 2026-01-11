# 🌱 SEEDERS & ADMIN CREATION GUIDE

Guide complet pour initialiser la base de données Noflaye MVP avec permissions, templates et données de démonstration.

---

## 📋 TABLE DES MATIÈRES

1. [Vue d'ensemble](#vue-densemble)
2. [Commande Quick Start](#commande-quick-start)
3. [Seeders Disponibles](#seeders-disponibles)
4. [Données Créées](#données-créées)
5. [Exemples d'Utilisation](#exemples-dutilisation)
6. [Multi-tenant Examples](#multi-tenant-examples)
7. [Credentials de Connexion](#credentials-de-connexion)

---

## 🎯 VUE D'ENSEMBLE

Le système de seeding comprend :

- **PermissionSystemSeeder** : Groupes, permissions, templates
- **DemoDataSeeder** : Shops, kitchens, users multi-tenant
- **DatabaseSeeder** : Orchestrateur principal

Plus une **commande Artisan** pour créer rapidement un super admin.

---

## ⚡ COMMANDE QUICK START

### **Créer un Super Admin (recommandé pour démarrer)**

```bash
# Interactive
php artisan make:super-admin

# Avec options
php artisan make:super-admin \
    --name="John Doe" \
    --email="john@noflaye.com" \
    --password="SecurePass123" \
    --force

# Avec valeurs par défaut
php artisan make:super-admin --force
```

**Output** :
```
🔐 Creating Super Administrator...

📋 Admin Details:
┌──────────┬─────────────────────┐
│ Field    │ Value               │
├──────────┼─────────────────────┤
│ Name     │ John Doe            │
│ Email    │ john@noflaye.com    │
│ Password │ ••••••••••••        │
└──────────┴─────────────────────┘

✅ Super admin created successfully!

🎉 Login credentials:
   Email: john@noflaye.com
   Password: SecurePass123

🔗 Access panels:
   Admin Panel: http://localhost/admin

⚠️  Please change the password after first login!
```

---

## 📦 SEEDERS DISPONIBLES

### **1. PermissionSystemSeeder**

Crée la structure complète du système de permissions.

```bash
php artisan db:seed --class=PermissionSystemSeeder
```

**Ce qui est créé** :

#### **Permission Groups (8)**
- User Management
- Shop Management
- Kitchen Management
- Driver Management
- Supervisor Management
- Supplier Management
- Permission Management
- System

#### **Permissions (79+)**
Toutes les permissions définies dans `Permission` enum :
- `users.viewAny`, `users.view`, `users.create`, etc.
- `shops.viewAny`, `shops.view`, `shops.create`, etc.
- `kitchens.*`, `drivers.*`, `supervisors.*`, etc.

#### **Templates (10)**
| Template | Level | Description |
|----------|-------|-------------|
| Super Admin | 0 | Full system access |
| Admin | 1 | Administrative access |
| Shop Manager | 2 | Manage shops |
| Shop Staff | 3 | Work in shops |
| Kitchen Manager | 2 | Manage kitchens |
| Kitchen Staff | 3 | Work in kitchens |
| Driver | 3 | Deliver orders |
| Supervisor | 2 | Supervise operations |
| Supplier Manager | 2 | Manage suppliers |
| Customer | 4 | Order and track |

#### **Template Permissions Assignment**
Chaque template reçoit automatiquement ses permissions appropriées.

---

### **2. DemoDataSeeder**

Crée des données de démonstration réalistes avec exemples multi-tenant.

```bash
php artisan db:seed --class=DemoDataSeeder
```

**Ce qui est créé** :

#### **Shops (3)**
- Noflaye Downtown
- Noflaye Plateau
- Noflaye Almadies

#### **Kitchens (2)**
- Central Kitchen
- Downtown Kitchen

#### **Suppliers (2)**
- Fresh Foods Supply
- Packaging Solutions

#### **Users (10)**
Voir [section Multi-tenant Examples](#multi-tenant-examples) pour détails.

---

### **3. DatabaseSeeder (Principal)**

Orchestre tous les seeders avec mode interactif.

```bash
# Mode interactif
php artisan db:seed

# Forcer tout
php artisan db:seed --force

# Seed spécifique
php artisan db:seed --class=PermissionSystemSeeder
```

**Flow interactif** :
```
🌱 Starting Database Seeding...

 Seed everything (permissions + demo data)? (yes/no) [yes]:
 > yes

📦 Seeding all data...

🔐 Seeding Permission System...
📁 Creating permission groups...
   Created 8 permission groups
🔑 Creating permissions from enum...
   Created 79 permissions, skipped 0
📋 Creating permission templates...
   Created 10 templates
🔗 Assigning permissions to templates...
   Super Administrator: 79 permissions
   Administrator: 45 permissions
   ...

✅ Permission System seeded successfully!

🎭 Seeding Demo Data...
🏪 Creating shops...
   ✓ Noflaye Downtown
   ✓ Noflaye Plateau
   ✓ Noflaye Almadies
🍳 Creating kitchens...
   ✓ Central Kitchen
   ✓ Downtown Kitchen
📦 Creating suppliers...
   ✓ Fresh Foods Supply
   ✓ Packaging Solutions
👥 Creating users...
   ✓ Super Admin (super@noflaye.com)
   ✓ Admin User (admin@noflaye.com)
   ...
🔗 Assigning users to entities...
   ✓ Alice Manager → Noflaye Downtown
   ✓ Charlie Multi → Noflaye Plateau
   ✓ Charlie Multi → Driver (Motorcycle)
   ...

✅ Demo data seeded successfully!

🔐 LOGIN CREDENTIALS
===================

┌──────────────────┬─────────────────────┬──────────┬──────────────────┐
│ Role             │ Email               │ Password │ Panel(s)         │
├──────────────────┼─────────────────────┼──────────┼──────────────────┤
│ Super Admin      │ super@noflaye.com   │ password │ Admin            │
│ Admin            │ admin@noflaye.com   │ password │ Admin            │
│ Shop Manager     │ alice@noflaye.com   │ password │ Shop             │
│ Kitchen Manager  │ bob@noflaye.com     │ password │ Kitchen          │
│ Manager + Driver │ charlie@noflaye.com │ password │ Shop, Driver     │
│ Staff + Driver   │ diana@noflaye.com   │ password │ Kitchen, Driver  │
│ Supervisor       │ eve@noflaye.com     │ password │ Supervisor       │
│ Multi-Shop Staff │ frank@noflaye.com   │ password │ Shop, Driver     │
│ Driver           │ grace@noflaye.com   │ password │ Driver           │
│ Customer         │ customer@noflaye.com│ password │ Customer         │
└──────────────────┴─────────────────────┴──────────┴──────────────────┘

⚠️  Default password for all accounts: password
⚠️  Please change passwords in production!

🔗 Access panels at:
   Admin: http://localhost/admin
   Shop: http://localhost/shop
   Kitchen: http://localhost/kitchen
   Driver: http://localhost/driver
   Supervisor: http://localhost/supervisor

✅ Database seeding completed!
```

---

## 📊 DONNÉES CRÉÉES

### **Permission System**

```
8 Permission Groups
├── User Management
├── Shop Management
├── Kitchen Management
├── Driver Management
├── Supervisor Management
├── Supplier Management
├── Permission Management
└── System

79+ Permissions
├── users.* (11 permissions)
├── shops.* (9 permissions)
├── kitchens.* (9 permissions)
├── drivers.* (7 permissions)
├── supervisors.* (7 permissions)
├── suppliers.* (7 permissions)
├── permissions.* (5 permissions)
├── templates.* (5 permissions)
├── delegations.* (7 permissions)
├── requests.* (7 permissions)
├── audit.* (3 permissions)
└── wildcards.* (5 permissions)

10 Permission Templates
├── Super Admin (ALL 79 permissions)
├── Admin (45 permissions)
├── Shop Manager (6 permissions)
├── Shop Staff (1 permission)
├── Kitchen Manager (5 permissions)
├── Kitchen Staff (1 permission)
├── Driver (2 permissions)
├── Supervisor (8 permissions)
├── Supplier Manager (4 permissions)
└── Customer (0 permissions)
```

### **Demo Data**

```
3 Shops
├── Noflaye Downtown (123 Main St)
├── Noflaye Plateau (456 Business Ave)
└── Noflaye Almadies (789 Beach Road)

2 Kitchens
├── Central Kitchen (100 Industrial Zone)
└── Downtown Kitchen (125 Main Street)

2 Suppliers
├── Fresh Foods Supply
└── Packaging Solutions

10 Users (Multi-tenant)
├── 1 Super Admin
├── 1 Admin
├── 1 Shop Manager (1 shop)
├── 1 Kitchen Manager (1 kitchen)
├── 1 Shop Manager + Driver (multi-tenant)
├── 1 Kitchen Staff + Driver (multi-tenant)
├── 1 Supervisor
├── 1 Shop Staff (2 shops + driver - multi-tenant)
├── 1 Pure Driver
└── 1 Customer

4 Driver Profiles
├── Charlie (Motorcycle, DRV-001)
├── Diana (Scooter, DRV-002)
├── Frank (Car, DRV-003)
└── Grace (Motorcycle, DRV-004)

1 Supervisor Profile
└── Eve
```

---

## 💡 EXEMPLES D'UTILISATION

### **Scenario 1 : Fresh Install**

```bash
# 1. Reset database
php artisan migrate:fresh

# 2. Seed everything
php artisan db:seed

# 3. Login as super admin
# Email: super@noflaye.com
# Password: password
```

### **Scenario 2 : Only Permissions**

```bash
# 1. Reset database
php artisan migrate:fresh

# 2. Seed only permissions
php artisan db:seed --class=PermissionSystemSeeder

# 3. Create your own admin
php artisan make:super-admin \
    --name="My Admin" \
    --email="me@company.com" \
    --password="SecurePassword123" \
    --force
```

### **Scenario 3 : Production Setup**

```bash
# 1. Migrate database
php artisan migrate

# 2. Seed ONLY permissions (no demo data)
php artisan db:seed --class=PermissionSystemSeeder

# 3. Create production admin
php artisan make:super-admin \
    --name="Production Admin" \
    --email="admin@production.com" \
    --password="VerySecurePassword!@#" \
    --force

# 4. Change password immediately after login!
```

### **Scenario 4 : Development with Demo Data**

```bash
# Full reset with demo data
php artisan migrate:fresh --seed

# Or step by step
php artisan migrate:fresh
php artisan db:seed --class=PermissionSystemSeeder
php artisan db:seed --class=DemoDataSeeder
```

---

## 🎭 MULTI-TENANT EXAMPLES

Le seeder crée plusieurs exemples de multi-tenancy (un user avec plusieurs rôles/panels).

### **Example 1 : Shop Manager + Driver**

**User** : Charlie Multi (`charlie@noflaye.com`)

**Templates** :
- Shop Manager (primary)
- Driver

**Accès** :
- Panel Shop : Gérer Noflaye Plateau
- Panel Driver : Livrer les commandes

**Use Case** : Le gérant peut aussi livrer pendant les heures creuses.

---

### **Example 2 : Kitchen Staff + Driver**

**User** : Diana Worker (`diana@noflaye.com`)

**Templates** :
- Kitchen Staff (primary)
- Driver

**Accès** :
- Panel Kitchen : Travailler à Downtown Kitchen
- Panel Driver : Livrer après service

**Use Case** : Staff cuisine qui livre après son shift.

---

### **Example 3 : Shop Staff Multi-Shop + Driver**

**User** : Frank Flexible (`frank@noflaye.com`)

**Templates** :
- Shop Staff (primary)
- Driver

**Accès** :
- Panel Shop : Travailler à Downtown ET Almadies
- Panel Driver : Livrer entre les deux boutiques

**Use Case** : Staff polyvalent qui travaille dans 2 boutiques + livre.

---

### **Example 4 : Supervisor (Global Access)**

**User** : Eve Supervisor (`eve@noflaye.com`)

**Template** :
- Supervisor

**Accès** :
- Voir TOUTES les boutiques
- Voir TOUTES les cuisines
- Voir TOUS les chauffeurs
- Assigner les chauffeurs

**Use Case** : Superviser les opérations sans gérer directement.

---

## 🔐 CREDENTIALS DE CONNEXION

### **Accounts Créés par le Seeder**

| Role | Email | Password | Primary Panel | Other Panels |
|------|-------|----------|---------------|--------------|
| Super Admin | super@noflaye.com | password | Admin | All |
| Admin | admin@noflaye.com | password | Admin | All |
| Shop Manager | alice@noflaye.com | password | Shop | - |
| Kitchen Manager | bob@noflaye.com | password | Kitchen | - |
| Shop Manager + Driver | charlie@noflaye.com | password | Shop | Driver |
| Kitchen Staff + Driver | diana@noflaye.com | password | Kitchen | Driver |
| Supervisor | eve@noflaye.com | password | Supervisor | - |
| Multi-Shop Staff | frank@noflaye.com | password | Shop | Driver |
| Driver | grace@noflaye.com | password | Driver | - |
| Customer | customer@noflaye.com | password | Customer | - |

### **Panel URLs**

```
Admin Panel:      http://localhost/admin
Shop Panel:       http://localhost/shop
Kitchen Panel:    http://localhost/kitchen
Driver Panel:     http://localhost/driver
Supervisor Panel: http://localhost/supervisor
Customer Panel:   http://localhost/customer
```

---

## 🔧 COMMANDES DISPONIBLES

### **Create Super Admin**

```bash
# Interactive
php artisan make:super-admin

# With options
php artisan make:super-admin \
    --name="Admin Name" \
    --email="admin@example.com" \
    --password="secure123" \
    --force

# Quick (uses defaults)
php artisan make:super-admin --force
```

### **Seed Database**

```bash
# All seeders (interactive)
php artisan db:seed

# Specific seeder
php artisan db:seed --class=PermissionSystemSeeder
php artisan db:seed --class=DemoDataSeeder

# Fresh migration + seed
php artisan migrate:fresh --seed

# Refresh without seed
php artisan migrate:fresh
php artisan db:seed --class=PermissionSystemSeeder
```

### **Generate Permissions from Enum**

```bash
# Generate missing permissions
php artisan permissions:generate-from-enum

# Dry run
php artisan permissions:generate-from-enum --dry-run

# Specific group
php artisan permissions:generate-from-enum --group="Custom Group"
```

---

## 📝 NOTES IMPORTANTES

### **⚠️ Security**

1. **Change default passwords** : Tous les comptes de demo utilisent `password`
2. **Delete demo data** : Ne pas utiliser les données de demo en production
3. **Create real admin** : Utiliser `make:super-admin` avec mot de passe fort

### **🎯 Best Practices**

1. **Production** :
   ```bash
   php artisan migrate
   php artisan db:seed --class=PermissionSystemSeeder
   php artisan make:super-admin --force
   # Change password after login!
   ```

2. **Development** :
   ```bash
   php artisan migrate:fresh --seed
   # Use demo accounts for testing
   ```

3. **Testing** :
   ```bash
   php artisan migrate:fresh
   php artisan db:seed --class=PermissionSystemSeeder
   # Create test users manually
   ```

### **🔄 Reset Database**

```bash
# Complete reset
php artisan migrate:fresh

# Reset + seed everything
php artisan migrate:fresh --seed

# Reset + only permissions
php artisan migrate:fresh
php artisan db:seed --class=PermissionSystemSeeder
```

---

## 🚀 QUICK START WORKFLOW

### **First Time Setup**

```bash
# 1. Clone project
git clone https://github.com/nakir00/noflaye-mvp.git
cd noflaye-mvp

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Create database
touch database/database.sqlite

# 5. Run migrations + seeders
php artisan migrate:fresh --seed

# 6. Start server
php artisan serve

# 7. Login
# URL: http://localhost:8000/admin
# Email: super@noflaye.com
# Password: password
```

### **Production Setup**

```bash
# 1. Run migrations only
php artisan migrate

# 2. Seed permissions
php artisan db:seed --class=PermissionSystemSeeder

# 3. Create admin
php artisan make:super-admin \
    --name="Production Admin" \
    --email="admin@production.com" \
    --password="VerySecurePassword123!" \
    --force

# 4. Change password after first login!
```

---

## 📚 RELATED DOCUMENTATION

- [Permission System Guide](PERMISSION_SYSTEM.md)
- [Policies Guide](POLICIES_GUIDE.md)
- [CRUD Actions Guide](CRUD_ACTIONS_GUIDE.md)
- [Base Resource Guide](BASE_RESOURCE.md)

---

**Dernière mise à jour** : 2026-01-03  
**Version** : 1.0
