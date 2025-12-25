<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class MyPermissions extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    protected static string $view = 'filament.pages.my-permissions';

    protected static ?string $navigationGroup = 'My Account';

    protected static ?string $title = 'My Permissions';

    public function getViewData(): array
    {
        $user = auth()->user();

        return [
            'user' => $user,
            'roles' => $user->roles()->with('permissions')->get(),
            'directPermissions' => $user->permissions,
            'inheritedPermissions' => $user->roles->flatMap->permissions->unique('id'),
            'groups' => $user->userGroups()->with('permissions')->get(),
        ];
    }
}
