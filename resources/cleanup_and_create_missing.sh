#!/bin/bash

# ========================================
#  CLEANUP & CREATE MISSING FILES
# ========================================

set -e

echo "========================================"
echo "  CLEANUP OBSOLETE + CREATE MISSING"
echo "========================================"
echo ""
echo "This script will:"
echo "  1. Remove obsolete DefaultPermissionTemplateResource"
echo "  2. Create UserPermission Model"
echo "  3. Verify Services exist (PermissionChecker, PermissionDelegator)"
echo "  4. Verify Observer exists (UserPermissionObserver)"
echo "  5. Verify Enums exist"
echo ""

read -p "Continue? (yes/no): " -r
echo ""

if [[ ! $REPLY =~ ^[Yy]es$ ]]; then
    echo "Aborted."
    exit 1
fi

# ========================================
# PART 1: REMOVE OBSOLETE FILES
# ========================================

echo "=========================================="
echo "PART 1: Removing Obsolete Files"
echo "=========================================="
echo ""

# Remove DefaultPermissionTemplateResource
if [ -f "app/Filament/Resources/DefaultPermissionTemplateResource.php" ]; then
    rm app/Filament/Resources/DefaultPermissionTemplateResource.php
    echo "✓ Removed: DefaultPermissionTemplateResource.php"
else
    echo "⚠ Already removed: DefaultPermissionTemplateResource.php"
fi

# Remove Pages directory if exists
if [ -d "app/Filament/Resources/DefaultPermissionTemplateResource" ]; then
    rm -rf app/Filament/Resources/DefaultPermissionTemplateResource/
    echo "✓ Removed: DefaultPermissionTemplateResource/ directory"
else
    echo "⚠ Already removed: DefaultPermissionTemplateResource/ directory"
fi

echo ""

# ========================================
# PART 2: CREATE USER PERMISSION MODEL
# ========================================

echo "=========================================="
echo "PART 2: Creating UserPermission Model"
echo "=========================================="
echo ""

if [ -f "app/Models/UserPermission.php" ]; then
    echo "⚠ UserPermission.php already exists"
    read -p "Overwrite? (yes/no): " -r
    echo ""
    if [[ ! $REPLY =~ ^[Yy]es$ ]]; then
        echo "Skipping UserPermission.php"
    else
        cat > app/Models/UserPermission.php << 'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * User Permission Model
 *
 * Represents the pivot table between users and permissions
 * but as a full Model to allow Eloquent queries in widgets/stats
 *
 * @property int $id
 * @property int $user_id
 * @property int $permission_id
 * @property int|null $scope_id
 * @property \Carbon\Carbon|null $expires_at
 * @property string|null $source
 * @property int|null $source_id
 * @property array|null $conditions
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class UserPermission extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'user_permissions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'permission_id',
        'scope_id',
        'expires_at',
        'source',
        'source_id',
        'conditions',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'conditions' => 'array',
    ];

    /**
     * Get the user that owns the permission
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the permission
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * Get the scope (if scoped)
     */
    public function scope(): BelongsTo
    {
        return $this->belongsTo(Scope::class);
    }

    /**
     * Check if permission is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if permission is active
     */
    public function isActive(): bool
    {
        return !$this->isExpired();
    }

    /**
     * Scope: Active permissions only
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope: Expired permissions
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
                     ->where('expires_at', '<=', now());
    }
}
EOF
        echo "✓ Created: app/Models/UserPermission.php"
    fi
else
    cat > app/Models/UserPermission.php << 'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * User Permission Model
 *
 * Represents the pivot table between users and permissions
 * but as a full Model to allow Eloquent queries in widgets/stats
 */
class UserPermission extends Model
{
    protected $table = 'user_permissions';

    protected $fillable = [
        'user_id',
        'permission_id',
        'scope_id',
        'expires_at',
        'source',
        'source_id',
        'conditions',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'conditions' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    public function scope(): BelongsTo
    {
        return $this->belongsTo(Scope::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return !$this->isExpired();
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
                     ->where('expires_at', '<=', now());
    }
}
EOF
    echo "✓ Created: app/Models/UserPermission.php"
fi

echo ""

# ========================================
# PART 3: VERIFY SERVICES
# ========================================

echo "=========================================="
echo "PART 3: Verifying Services"
echo "=========================================="
echo ""

# Check PermissionChecker
if [ -f "app/Services/Permissions/PermissionChecker.php" ]; then
    echo "✓ PermissionChecker.php exists"
else
    echo "⚠ PermissionChecker.php NOT FOUND"
    echo "  You need to create this service manually"
    echo "  Expected: app/Services/Permissions/PermissionChecker.php"
fi

# Check PermissionDelegator
if [ -f "app/Services/Permissions/PermissionDelegator.php" ]; then
    echo "✓ PermissionDelegator.php exists"
else
    echo "⚠ PermissionDelegator.php NOT FOUND"
    echo "  You need to create this service manually"
    echo "  Expected: app/Services/Permissions/PermissionDelegator.php"
fi

echo ""

# ========================================
# PART 4: VERIFY OBSERVER
# ========================================

echo "=========================================="
echo "PART 4: Verifying Observer"
echo "=========================================="
echo ""

if [ -f "app/Observers/UserPermissionObserver.php" ]; then
    echo "✓ UserPermissionObserver.php exists"
else
    echo "⚠ UserPermissionObserver.php NOT FOUND"
    echo "  You may need to create this observer"
    echo "  Expected: app/Observers/UserPermissionObserver.php"
fi

echo ""

# ========================================
# PART 5: VERIFY ENUMS
# ========================================

echo "=========================================="
echo "PART 5: Verifying Enums"
echo "=========================================="
echo ""

ENUMS=(
    "AuditAction.php"
    "ConditionType.php"
    "PermissionAction.php"
    "WildcardPattern.php"
)

for enum in "${ENUMS[@]}"; do
    if [ -f "app/Enums/$enum" ]; then
        echo "✓ $enum exists"
    else
        echo "⚠ $enum NOT FOUND"
    fi
done

echo ""

# ========================================
# PART 6: CLEAR CACHES
# ========================================

echo "=========================================="
echo "PART 6: Clearing Caches"
echo "=========================================="
echo ""

php artisan config:clear 2>/dev/null && echo "✓ Config cache cleared" || echo "⚠ Config cache had warnings"
php artisan route:clear 2>/dev/null && echo "✓ Route cache cleared" || echo "⚠ Route cache had warnings"
php artisan clear-compiled 2>/dev/null && echo "✓ Compiled cache cleared" || echo "⚠ Compiled cache had warnings"

echo ""

# ========================================
# SUCCESS
# ========================================

echo "=========================================="
echo "  ✓ CLEANUP & CREATION COMPLETED"
echo "=========================================="
echo ""
echo "Summary:"
echo "  Removed: DefaultPermissionTemplateResource (obsolete)"
echo "  Created: UserPermission Model"
echo ""
echo "Files verified:"
echo "  Services: PermissionChecker, PermissionDelegator"
echo "  Observer: UserPermissionObserver"
echo "  Enums: AuditAction, ConditionType, PermissionAction, WildcardPattern"
echo ""
echo "⚠ If any files were NOT FOUND, you need to create them manually"
echo ""
echo "Next steps:"
echo "  1. Verify all services/enums exist"
echo "  2. Run: php artisan about"
echo "  3. Test widgets (no 'Undefined type' errors)"
echo ""
echo "✓ Cleanup completed!"
