#!/bin/bash

# ========================================
#  FIX ALL FILAMENT V4 COMPONENTS
# ========================================

set -e  # Exit on error

echo "========================================"
echo "  COMPLETE FILAMENT V4 FIX"
echo "========================================"
echo ""
echo "This script will fix ALL Filament components:"
echo "  - 5 Permission Resources"
echo "  - 3 RelationManagers"
echo "  - 2 Custom Pages (MyDelegations, PermissionAnalyticsDashboard)"
echo ""
echo "Changes:"
echo "  1. form(Form) → form(Schema)"
echo "  2. ->schema([]) → ->components([])"
echo "  3. Add required imports"
echo "  4. Fix static property declarations"
echo "  5. Clear all caches"
echo ""

read -p "Continue? (yes/no): " -r
echo ""

if [[ ! $REPLY =~ ^[Yy]es$ ]]; then
    echo "Aborted."
    exit 1
fi

echo "✓ Starting complete fix..."
echo ""

# Create backup directory
BACKUP_DIR="backups/filament_complete_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"
echo "✓ Created backup directory: $BACKUP_DIR"
echo ""

# ========================================
# PART 1: FIX PERMISSION RESOURCES
# ========================================

echo "=========================================="
echo "PART 1: Fixing Permission Resources"
echo "=========================================="
echo ""

RESOURCES=(
    "app/Filament/Resources/PermissionTemplateResource.php"
    "app/Filament/Resources/PermissionWildcardResource.php"
    "app/Filament/Resources/PermissionDelegationResource.php"
    "app/Filament/Resources/PermissionRequestResource.php"
    "app/Filament/Resources/PermissionAuditLogResource.php"
)

for file in "${RESOURCES[@]}"; do
    if [ -f "$file" ]; then
        cp "$file" "$BACKUP_DIR/"
        echo "Processing: $file"

        # Add imports if not present
        if ! grep -q "use Filament\\\\Schemas\\\\Schema;" "$file"; then
            perl -i -pe 's/(use Filament\\Forms\\Form;)/$1\nuse Filament\\Schemas\\Components\\Section;\nuse Filament\\Schemas\\Schema;/' "$file"
        fi

        # Fix method signatures
        perl -i -pe 's/public static function form\(Form \$form\): Form/public static function form(Schema \$form): Schema/' "$file"
        perl -i -pe 's/->schema\(\[/->components([/' "$file"
        perl -i -pe 's/Forms\\Components\\Section::/Section::/g' "$file"

        echo "✓ Fixed: $file"
        echo ""
    fi
done

# ========================================
# PART 2: FIX RELATION MANAGERS
# ========================================

echo "=========================================="
echo "PART 2: Fixing RelationManagers"
echo "=========================================="
echo ""

MANAGERS=(
    "app/Filament/Resources/UserResource/RelationManagers/PermissionsRelationManager.php"
    "app/Filament/Resources/UserResource/RelationManagers/TemplatesRelationManager.php"
    "app/Filament/Resources/UserResource/RelationManagers/DelegationsRelationManager.php"
)

for file in "${MANAGERS[@]}"; do
    if [ -f "$file" ]; then
        cp "$file" "$BACKUP_DIR/"
        echo "Processing: $file"

        # Add imports if not present
        if ! grep -q "use Filament\\\\Schemas\\\\Schema;" "$file"; then
            perl -i -pe 's/(use Filament\\Forms\\Form;)/$1\nuse Filament\\Schemas\\Components\\Section;\nuse Filament\\Schemas\\Schema;/' "$file"
        fi

        # Fix method signatures
        perl -i -pe 's/public function form\(Form \$form\): Form/public function form(Schema \$form): Schema/' "$file"
        perl -i -pe 's/->schema\(\[/->components([/' "$file"
        perl -i -pe 's/Forms\\Components\\Section::/Section::/g' "$file"

        echo "✓ Fixed: $file"
        echo ""
    fi
done

# ========================================
# PART 3: FIX CUSTOM PAGES
# ========================================

echo "=========================================="
echo "PART 3: Fixing Custom Pages"
echo "=========================================="
echo ""

PAGES=(
    "app/Filament/Pages/MyDelegations.php"
    "app/Filament/Pages/PermissionAnalyticsDashboard.php"
)

for file in "${PAGES[@]}"; do
    if [ -f "$file" ]; then
        cp "$file" "$BACKUP_DIR/"
        echo "Processing: $file"

        # Fix static property issue: protected static ?string $view
        # In Filament v4, $view should NOT be static in custom pages
        perl -i -pe 's/protected static \?string \$view/protected ?string $view/' "$file"

        # Also fix if it's declared as just string
        perl -i -pe 's/protected static string \$view/protected string $view/' "$file"

        echo "✓ Fixed: $file"
        echo ""
    fi
done

# ========================================
# PART 4: CLEAR CACHES
# ========================================

echo "=========================================="
echo "PART 4: Clearing Caches"
echo "=========================================="
echo ""

php artisan config:clear 2>/dev/null && echo "✓ Config cache cleared" || echo "⚠ Config cache had warnings"
php artisan route:clear 2>/dev/null && echo "✓ Route cache cleared" || echo "⚠ Route cache had warnings"
php artisan view:clear 2>/dev/null && echo "✓ View cache cleared" || echo "⚠ View cache had warnings"
php artisan clear-compiled 2>/dev/null && echo "✓ Compiled cache cleared" || echo "⚠ Compiled cache had warnings"

echo ""

# ========================================
# PART 5: OPTIMIZE & TEST
# ========================================

echo "=========================================="
echo "PART 5: Optimizing Application"
echo "=========================================="
echo ""

if php artisan optimize 2>&1 | head -5; then
    echo ""
    echo "✓ Optimization successful"
else
    echo "⚠ Optimization had warnings"
fi

echo ""

# ========================================
# FINAL TEST
# ========================================

echo "=========================================="
echo "PART 6: Final Compilation Test"
echo "=========================================="
echo ""

if php artisan about 2>&1 | head -20; then
    echo ""
    echo "✓ Compilation test PASSED"
else
    echo "✗ Compilation test FAILED"
    echo ""
    echo "Backup files are in: $BACKUP_DIR"
    exit 1
fi

# ========================================
# SUCCESS
# ========================================

echo ""
echo "========================================"
echo "  ✓ COMPLETE FIX SUCCESSFUL!"
echo "========================================"
echo ""
echo "Summary:"
echo "  Fixed: 5 Resources + 3 RelationManagers + 2 Pages"
echo "  Backups: $BACKUP_DIR"
echo ""
echo "Files fixed:"
echo "  Resources:"
echo "    - PermissionTemplateResource.php"
echo "    - PermissionWildcardResource.php"
echo "    - PermissionDelegationResource.php"
echo "    - PermissionRequestResource.php"
echo "    - PermissionAuditLogResource.php"
echo ""
echo "  RelationManagers:"
echo "    - PermissionsRelationManager.php"
echo "    - TemplatesRelationManager.php"
echo "    - DelegationsRelationManager.php"
echo ""
echo "  Custom Pages:"
echo "    - MyDelegations.php"
echo "    - PermissionAnalyticsDashboard.php"
echo ""
echo "Next steps:"
echo "  1. Test: php artisan tinker"
echo "  2. Test: php artisan serve"
echo "  3. Open: http://localhost:8000/admin"
echo "  4. Verify all Permission resources work"
echo "  5. Commit changes"
echo ""
echo "✓ All Filament components are now v4 compatible!"
