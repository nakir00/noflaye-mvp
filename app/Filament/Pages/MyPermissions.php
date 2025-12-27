<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class MyPermissions extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-lock-closed';

    protected string $view = 'filament.pages.my-permissions';

    protected static string|UnitEnum|null $navigationGroup = 'My Account';

    protected static ?string $title = 'My Permissions';

    public function getViewData(): array
    {
        $user = Auth::user();

        return [
            'user' => $user,
            'roles' => $user->roles()->with('permissions')->get(),
            'directPermissions' => $user->permissions,
            'inheritedPermissions' => $user->roles->flatMap->permissions->unique('id'),
            'groups' => $user->userGroups()->with('permissions')->get(),
        ];
    }
}
