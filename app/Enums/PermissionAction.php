<?php

namespace App\Enums;

/**
 * Enum: PermissionAction
 *
 * Purpose: Define standard CRUD and management actions for permissions
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
enum PermissionAction: string
{
    // CRUD standard
    case CREATE = 'create';
    case READ = 'read';
    case UPDATE = 'update';
    case DELETE = 'delete';

    // Extended read actions
    case VIEW = 'view';
    case LIST = 'list';

    // Export/Import actions
    case EXPORT = 'export';
    case IMPORT = 'import';

    // Archive actions
    case ARCHIVE = 'archive';
    case RESTORE = 'restore';

    // Management actions
    case MANAGE = 'manage';
    case ADMIN = 'admin';

    /**
     * Get all write actions (create, update, delete)
     *
     * @return array<string>
     */
    public static function writeActions(): array
    {
        return [
            self::CREATE->value,
            self::UPDATE->value,
            self::DELETE->value,
        ];
    }

    /**
     * Get all read actions (read, view, list)
     *
     * @return array<string>
     */
    public static function readActions(): array
    {
        return [
            self::READ->value,
            self::VIEW->value,
            self::LIST->value,
        ];
    }

    /**
     * Get all admin actions (full CRUD + export + import + manage + admin)
     *
     * @return array<string>
     */
    public static function adminActions(): array
    {
        return [
            self::CREATE->value,
            self::READ->value,
            self::UPDATE->value,
            self::DELETE->value,
            self::EXPORT->value,
            self::IMPORT->value,
            self::MANAGE->value,
            self::ADMIN->value,
        ];
    }

    /**
     * Get all management actions (admin, manage, archive, restore)
     *
     * @return array<string>
     */
    public static function managementActions(): array
    {
        return [
            self::MANAGE->value,
            self::ADMIN->value,
            self::ARCHIVE->value,
            self::RESTORE->value,
        ];
    }

    /**
     * Check if action is a write action
     */
    public function isWrite(): bool
    {
        return in_array($this->value, self::writeActions());
    }

    /**
     * Check if action is a read action
     */
    public function isRead(): bool
    {
        return in_array($this->value, self::readActions());
    }

    /**
     * Get action description
     */
    public function description(): string
    {
        return match ($this) {
            self::CREATE => 'Create new resources',
            self::READ => 'Read resource details',
            self::UPDATE => 'Update existing resources',
            self::DELETE => 'Delete resources permanently',
            self::VIEW => 'View resource in detail',
            self::LIST => 'List all resources',
            self::EXPORT => 'Export resources to file',
            self::IMPORT => 'Import resources from file',
            self::ARCHIVE => 'Archive resources (soft delete)',
            self::RESTORE => 'Restore archived resources',
            self::MANAGE => 'Full management access',
            self::ADMIN => 'Full administrative access',
        };
    }

    /**
     * Get Heroicon for action
     */
    public function icon(): string
    {
        return match ($this) {
            self::CREATE => 'heroicon-o-plus-circle',
            self::READ => 'heroicon-o-eye',
            self::UPDATE => 'heroicon-o-pencil',
            self::DELETE => 'heroicon-o-trash',
            self::VIEW => 'heroicon-o-document-magnifying-glass',
            self::LIST => 'heroicon-o-list-bullet',
            self::EXPORT => 'heroicon-o-arrow-down-tray',
            self::IMPORT => 'heroicon-o-arrow-up-tray',
            self::ARCHIVE => 'heroicon-o-archive-box',
            self::RESTORE => 'heroicon-o-arrow-uturn-left',
            self::MANAGE => 'heroicon-o-cog-6-tooth',
            self::ADMIN => 'heroicon-o-shield-check',
        };
    }

    /**
     * Get color for Filament badge
     */
    public function color(): string
    {
        return match ($this) {
            self::CREATE => 'success',
            self::READ, self::VIEW, self::LIST => 'info',
            self::UPDATE => 'warning',
            self::DELETE => 'danger',
            self::EXPORT, self::IMPORT => 'gray',
            self::ARCHIVE => 'warning',
            self::RESTORE => 'success',
            self::MANAGE, self::ADMIN => 'primary',
        };
    }
}
