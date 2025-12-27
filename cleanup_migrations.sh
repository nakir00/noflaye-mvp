#!/bin/bash
# ======================================================================
# MIGRATION CLEANUP SCRIPT
# ======================================================================
# Project: Noflaye Box MVP
# Date: 2025-12-27
# Purpose: Safely backup and remove obsolete migration files
#
# IMPORTANT: Run this script ONLY AFTER:
# 1. All data migrations (100001-100006) have completed successfully
# 2. Cleanup migration (200001) has been executed in production
# 3. Full database backup has been created
# 4. Code has been updated (RegisterController, User model)
# 5. Testing completed successfully
# ======================================================================

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Counters
BACKED_UP=0
DELETED=0

echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}  NOFLAYE BOX - MIGRATION CLEANUP${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

# Confirmation prompt
echo -e "${RED}⚠️  WARNING: This script will DELETE obsolete migration files${NC}"
echo -e "${YELLOW}Before proceeding, ensure:${NC}"
echo "  [1] Data migrations (100001-100006) completed successfully"
echo "  [2] Cleanup migration (200001) executed in production"
echo "  [3] Full database backup created"
echo "  [4] Code updated (RegisterController, User model)"
echo "  [5] All tests passing"
echo ""
read -p "Have you completed all prerequisites? (yes/no): " confirmation

if [ "$confirmation" != "yes" ]; then
    echo -e "${RED}❌ Operation cancelled${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}✓ Starting cleanup process...${NC}"
echo ""

# Create backup directory
BACKUP_DIR="database/migrations_backup"
mkdir -p $BACKUP_DIR
echo -e "${GREEN}✓ Created backup directory: $BACKUP_DIR${NC}"

# Create log file
LOG_FILE="migration_cleanup_$(date +%Y%m%d_%H%M%S).log"
echo "Migration Cleanup Log - $(date)" > $LOG_FILE
echo "========================================" >> $LOG_FILE
echo "" >> $LOG_FILE

# Function to backup and remove file
backup_and_remove() {
    local file=$1
    local reason=$2

    if [ -f "database/migrations/$file" ]; then
        # Backup
        cp "database/migrations/$file" "$BACKUP_DIR/$file"
        BACKED_UP=$((BACKED_UP + 1))

        # Remove
        rm "database/migrations/$file"
        DELETED=$((DELETED + 1))

        # Log
        echo "DELETED: $file" >> $LOG_FILE
        echo "  Reason: $reason" >> $LOG_FILE
        echo "" >> $LOG_FILE

        echo -e "${GREEN}✓${NC} $file"
    else
        echo -e "${YELLOW}⚠${NC} $file (not found, skipping)"
        echo "SKIPPED: $file (file not found)" >> $LOG_FILE
        echo "" >> $LOG_FILE
    fi
}

echo ""
echo -e "${YELLOW}Removing Old RBAC System Migrations...${NC}"
echo "----------------------------------------"

backup_and_remove "2025_12_21_125132_create_roles_table.php" \
    "Ancien système RBAC - remplacé par permission_templates"

backup_and_remove "2025_12_21_125143_create_role_permissions_table.php" \
    "Ancien système RBAC - remplacé par template_permissions"

backup_and_remove "2025_12_21_125144_create_user_roles_table.php" \
    "Ancien système RBAC - remplacé par user_templates"

backup_and_remove "2025_12_21_140002_create_role_hierarchy_table.php" \
    "Ancien système RBAC - remplacé par permission_template_hierarchy"

backup_and_remove "2025_12_21_232134_create_default_permission_templates_table.php" \
    "Ancien système templates - remplacé par permission_templates unifié"

echo ""
echo -e "${YELLOW}Removing Old Enhancement Migrations...${NC}"
echo "----------------------------------------"

backup_and_remove "2025_12_21_125613_add_primary_role_to_users_table.php" \
    "Colonne primary_role_id obsolète - remplacée par primary_template_id"

backup_and_remove "2025_12_21_140000_add_scope_and_validity_to_user_roles_table.php" \
    "Table user_roles obsolète"

backup_and_remove "2025_12_21_140001_create_user_permissions_table.php" \
    "Version obsolète avec ancien système scope_type polymorphique"

echo ""
echo -e "${YELLOW}Removing Old Template Pivots...${NC}"
echo "----------------------------------------"

backup_and_remove "2025_12_25_170551_create_template_pivots_tables.php" \
    "Pivots pour ancien système default_permission_templates"

echo ""
echo -e "${YELLOW}Removing One-Time Data Migrations...${NC}"
echo "----------------------------------------"

backup_and_remove "2025_12_26_100001_create_scopes_from_existing_data.php" \
    "Data migration one-time - déjà exécutée"

backup_and_remove "2025_12_26_100002_migrate_roles_to_templates.php" \
    "Data migration one-time - déjà exécutée"

backup_and_remove "2025_12_26_100003_migrate_default_templates_to_templates.php" \
    "Data migration one-time - déjà exécutée"

backup_and_remove "2025_12_26_100004_migrate_role_permissions_to_template_permissions.php" \
    "Data migration one-time - déjà exécutée"

backup_and_remove "2025_12_26_100005_migrate_user_roles_to_user_templates.php" \
    "Data migration one-time - déjà exécutée"

backup_and_remove "2025_12_26_100006_rebuild_all_hierarchies.php" \
    "Data migration one-time - déjà exécutée"

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  CLEANUP COMPLETE${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "Summary:"
echo -e "  ${GREEN}Backed up:${NC} $BACKED_UP files to $BACKUP_DIR"
echo -e "  ${GREEN}Deleted:${NC} $DELETED files from database/migrations"
echo -e "  ${GREEN}Log file:${NC} $LOG_FILE"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "  1. Review log file: cat $LOG_FILE"
echo "  2. Verify migrations: php artisan migrate:status"
echo "  3. Test fresh migration: php artisan migrate:fresh --seed (on staging!)"
echo "  4. If all OK, commit changes to git"
echo ""
echo -e "${GREEN}✓ Cleanup completed successfully!${NC}"
