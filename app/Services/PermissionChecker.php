<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\User;

class PermissionChecker
{
    public function __construct(
        protected ContextRuleEvaluator $contextRuleEvaluator
    ) {}

    /**
     * Vérifie si un utilisateur a une permission
     */
    public function check(
        User $user,
        string $permissionSlug,
        ?string $scopeType = null,
        ?int $scopeId = null,
        array $context = []
    ): bool {
        // Super Admin a toutes les permissions
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // 1. Récupérer la permission
        $permission = Permission::where('slug', $permissionSlug)
            ->where('active', true)
            ->first();

        if (!$permission) {
            return false;
        }

        // 2. Vérifier permissions directes (PRIORITÉ 1)
        $directPermission = $this->checkDirectPermission($user, $permission, $scopeType, $scopeId);

        if ($directPermission !== null) {
            return $directPermission;
        }

        // 3. Vérifier permissions via groupes (PRIORITÉ 2)
        $groupPermission = $this->checkGroupPermission($user, $permission, $scopeType, $scopeId);

        if ($groupPermission !== null) {
            return $groupPermission;
        }

        // 4. Vérifier permissions via rôles (PRIORITÉ 3)
        $rolePermission = $this->checkRolePermission($user, $permission, $scopeType, $scopeId);

        if (!$rolePermission) {
            return false;
        }

        // 5. Évaluer context rules
        return $this->contextRuleEvaluator->evaluate($user, $permission, $context);
    }

    /**
     * Vérifie les permissions directes de l'utilisateur
     *
     * @return bool|null true=granted, false=revoked, null=not found
     */
    protected function checkDirectPermission(User $user, Permission $permission, ?string $scopeType, ?int $scopeId): ?bool
    {
        $query = $user->permissions()
            ->where('permissions.id', $permission->id);

        // Filtrer par scope si fourni
        if ($scopeType !== null) {
            $query->where('user_permissions.scope_type', $scopeType);

            if ($scopeId !== null) {
                $query->where('user_permissions.scope_id', $scopeId);
            }
        } else {
            $query->whereNull('user_permissions.scope_type');
        }

        $userPermission = $query->first();

        if (!$userPermission) {
            return null;
        }

        // Si c'est un revoke, retourner false
        if ($userPermission->pivot->permission_type === 'revoke') {
            return false;
        }

        // Sinon c'est un grant, retourner true
        return true;
    }

    /**
     * Vérifie les permissions via les groupes utilisateur
     *
     * @return bool|null true=granted, false=revoked, null=not found
     */
    protected function checkGroupPermission(User $user, Permission $permission, ?string $scopeType, ?int $scopeId): ?bool
    {
        $userGroups = $user->userGroups()
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('user_group_members.valid_until')
                        ->orWhere('user_group_members.valid_until', '>', now());
                })
                ->where(function ($q) {
                    $q->whereNull('user_group_members.valid_from')
                        ->orWhere('user_group_members.valid_from', '<=', now());
                });
            })
            ->get();

        if ($userGroups->isEmpty()) {
            return null;
        }

        foreach ($userGroups as $group) {
            $groupPermission = $group->permissions()
                ->where('permissions.id', $permission->id)
                ->first();

            if ($groupPermission) {
                // Vérifier le scope
                if ($scopeType !== null && $group->scope_type !== $scopeType) {
                    continue;
                }

                if ($scopeId !== null && $group->scope_id !== $scopeId) {
                    continue;
                }

                // Retourner selon le type de permission
                if ($groupPermission->pivot->permission_type === 'revoke') {
                    return false;
                }

                return true;
            }
        }

        return null;
    }

    /**
     * Vérifie les permissions via les rôles de l'utilisateur
     */
    protected function checkRolePermission(User $user, Permission $permission, ?string $scopeType, ?int $scopeId): bool
    {
        $roles = $user->roles()->get();

        // Ajouter le rôle primaire
        if ($user->primaryRole) {
            $roles->push($user->primaryRole);
        }

        foreach ($roles as $role) {
            $hasPermission = $role->permissions()
                ->where('permissions.id', $permission->id)
                ->exists();

            if ($hasPermission) {
                // Vérifier le scope si le rôle est scopé
                if (isset($role->pivot)) {
                    if ($scopeType !== null && $role->pivot->scope_type !== $scopeType) {
                        continue;
                    }

                    if ($scopeId !== null && $role->pivot->scope_id !== $scopeId) {
                        continue;
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie si l'utilisateur peut accorder/révoquer une permission
     */
    public function canManagePermission(User $user, string $permissionSlug): bool
    {
        // Super Admin peut tout gérer
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admin peut gérer sauf les permissions système
        if ($user->hasRole('admin')) {
            $permission = Permission::where('slug', $permissionSlug)->first();
            return $permission && !$permission->is_system;
        }

        // Autres utilisateurs ne peuvent pas gérer les permissions
        return false;
    }
}
