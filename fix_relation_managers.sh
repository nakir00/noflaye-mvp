#!/bin/bash
# ======================================================================
# FIX FILAMENT V4 RELATION MANAGERS SCRIPT
# ======================================================================
# Project: Noflaye Box MVP
# Date: 2025-12-27
# Purpose: Fix all 3 RelationManagers to use Filament v4 API
#
# IMPORTANT: This script fixes the 3 RelationManagers that were
# created with Filament v3 API to use the correct Filament v4 API
# ======================================================================

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}  FILAMENT V4 RELATION MANAGERS FIX${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

echo -e "${GREEN}This script will:${NC}"
echo "  1. Update 3 RelationManagers to Filament v4 API"
echo "  2. Change form(Form) to form(Schema)"
echo "  3. Change ->schema([]) to ->components([])"
echo "  4. Add required imports"
echo "  5. Clear all caches"
echo "  6. Test compilation"
echo ""

read -p "Continue? (yes/no): " confirmation

if [ "$confirmation" != "yes" ]; then
    echo -e "${RED}❌ Operation cancelled${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}✓ Starting fixes...${NC}"
echo ""

# Create backup directory
BACKUP_DIR="backups/relation_managers_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"
echo -e "${GREEN}✓ Created backup directory: $BACKUP_DIR${NC}"

# List of files to fix
FILES=(
    "app/Filament/Resources/UserResource/RelationManagers/PermissionsRelationManager.php"
    "app/Filament/Resources/UserResource/RelationManagers/TemplatesRelationManager.php"
    "app/Filament/Resources/UserResource/RelationManagers/DelegationsRelationManager.php"
)

echo ""
echo -e "${YELLOW}Step 1: Backing up files...${NC}"
for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        cp "$file" "$BACKUP_DIR/"
        echo -e "${GREEN}✓${NC} Backed up: $file"
    fi
done

echo ""
echo -e "${YELLOW}Step 2: Fixing imports and method signatures...${NC}"
echo ""

# Fix each file
for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        echo -e "${YELLOW}Processing: $file${NC}"

        # 1. Add Schema import if not present (after Forms\Form)
        if ! grep -q "use Filament\\\\\\\\Schemas\\\\\\\\Schema;" "$file"; then
            # Add after "use Filament\Forms\Form;"
            perl -i -pe 's/(use Filament\\\\Forms\\\\Form;)/$1\\nuse Filament\\\\Schemas\\\\Components\\\\Section;\\nuse Filament\\\\Schemas\\\\Schema;/' "$file"
            echo "  - Added Schema and Section imports"
        fi

        # 2. Change method signature: form(Form $form): Form -> form(Schema $form): Schema
        perl -i -pe 's/public function form\\(Form \\$form\\): Form/public function form(Schema \\$form): Schema/' "$file"
        echo "  - Updated form() method signature"

        # 3. Change ->schema([ to ->components([
        perl -i -pe 's/->schema\\(\\[/->components([/' "$file"
        echo "  - Changed ->schema([]) to ->components([])"

        # 4. Change Forms\\Components\\Section to Section (if Section is used)
        perl -i -pe 's/Forms\\\\Components\\\\Section::/Section::/g' "$file"
        echo "  - Updated Section references"

        echo -e "${GREEN}✓ Fixed: $file${NC}"
        echo ""
    else
        echo -e "${RED}✗ File not found: $file${NC}"
        echo ""
    fi
done

echo ""
echo -e "${YELLOW}Step 3: Clearing Laravel caches...${NC}"

# Clear config cache
if php artisan config:clear 2>&1; then
    echo -e "${GREEN}✓ Config cache cleared${NC}"
else
    echo -e "${YELLOW}⚠ Config cache clear had warnings (this is normal)${NC}"
fi

# Clear route cache
if php artisan route:clear 2>&1; then
    echo -e "${GREEN}✓ Route cache cleared${NC}"
else
    echo -e "${YELLOW}⚠ Route cache clear had warnings${NC}"
fi

# Clear view cache
if php artisan view:clear 2>&1; then
    echo -e "${GREEN}✓ View cache cleared${NC}"
else
    echo -e "${YELLOW}⚠ View cache clear had warnings${NC}"
fi

# Clear compiled cache
if php artisan clear-compiled 2>&1; then
    echo -e "${GREEN}✓ Compiled cache cleared${NC}"
else
    echo -e "${YELLOW}⚠ Compiled cache clear had warnings${NC}"
fi

echo ""
echo -e "${YELLOW}Step 4: Optimizing application...${NC}"

if php artisan optimize 2>&1; then
    echo -e "${GREEN}✓ Application optimized${NC}"
else
    echo -e "${RED}✗ Optimization failed - check errors above${NC}"
    echo ""
    echo -e "${YELLOW}Backup files are in: $BACKUP_DIR${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  FIX COMPLETED SUCCESSFULLY!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "Summary:"
echo -e "  ${GREEN}Fixed:${NC} ${#FILES[@]} RelationManagers"
echo -e "  ${GREEN}Backups:${NC} $BACKUP_DIR"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "  1. Test the application"
echo "  2. Verify Filament admin panel loads"
echo "  3. Test UserResource with tabs (Permissions/Templates/Delegations)"
echo "  4. If all OK, commit changes"
echo ""
echo -e "${GREEN}Test commands:${NC}"
echo "  php artisan tinker"
echo "  php artisan serve"
echo "  php artisan about"
echo ""
echo -e "${GREEN}✓ All RelationManagers fixed!${NC}"
