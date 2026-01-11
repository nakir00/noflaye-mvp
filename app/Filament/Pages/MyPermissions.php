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

    public function getUserProperty()
    {
        return Auth::user();
    }

    public function getTemplatesProperty()
    {
        return $this->user->templates()
            ->with(['permissions', 'wildcards'])
            ->withPivot(['scope_id', 'template_version', 'auto_upgrade', 'auto_sync', 'valid_from', 'valid_until'])
            ->get();
    }

    public function getPrimaryTemplateProperty()
    {
        return $this->user->primaryTemplate;
    }

    public function getDirectPermissionsProperty()
    {
        return $this->user->directPermissions()->with('group')->get();
    }

    public function getAllPermissionsProperty()
    {
        return $this->user->getAllPermissions();
    }

    public function getEffectivePermissionsProperty()
    {
        return $this->user->getEffectivePermissions();
    }

    public function getDelegationsProperty()
    {
        return $this->user->delegationsReceived()->with(['delegator', 'permission'])->get();
    }
}
