<?php

use App\Models\User;
use App\Models\Shop;
use App\Models\Kitchen;
use App\Models\Driver;
use App\Models\Supervisor;
use App\Models\Role;
use App\Models\Permission;
use App\Models\DefaultPermissionTemplate;

test('multi-panel user can access all their panels', function () {
    $user = User::factory()->create();

    $shop = Shop::factory()->create();
    $kitchen = Kitchen::factory()->create();
    $driver = Driver::factory()->create();
    $supervisor = Supervisor::factory()->create();

    $user->shops()->attach($shop->id);
    $user->kitchens()->attach($kitchen->id);
    $user->drivers()->attach($driver->id);
    $user->supervisors()->attach($supervisor->id);

    $panels = $user->getAccessiblePanels();

    expect($panels)->toHaveCount(4);
    expect($panels[0]['id'])->toBe('shop');
    expect($panels[0]['entities'])->toHaveCount(1);
    expect($panels[1]['id'])->toBe('kitchen');
});

test('user sees managed entities in each panel', function () {
    $user = User::factory()->create();

    $shop1 = Shop::factory()->create();
    $shop2 = Shop::factory()->create();

    $user->shops()->attach([$shop1->id, $shop2->id]);

    $managedShops = $user->getManagedShops();

    expect($managedShops)->toHaveCount(2);
    expect($user->managesShop($shop1->id))->toBeTrue();
    expect($user->managesShop($shop2->id))->toBeTrue();
});

test('template applies roles and permissions to user', function () {
    $user = User::factory()->create();
    $template = DefaultPermissionTemplate::factory()->create([
        'scope_type' => 'shop',
        'scope_id' => 1,
    ]);

    $role = Role::factory()->create();
    $permission = Permission::factory()->create();

    $template->roles()->attach($role->id);
    $template->permissions()->attach($permission->id);

    $template->applyToUser($user);

    expect($user->fresh()->roles)->toHaveCount(1);
    expect($user->fresh()->permissions)->toHaveCount(1);
});

test('cross-entity links work correctly', function () {
    $shop = Shop::factory()->create();
    $kitchen = Kitchen::factory()->create();
    $driver = Driver::factory()->create();

    $shop->kitchens()->attach($kitchen->id);
    $shop->drivers()->attach($driver->id);
    $kitchen->drivers()->attach($driver->id);

    expect($shop->kitchens)->toHaveCount(1);
    expect($shop->drivers)->toHaveCount(1);
    expect($kitchen->shops)->toHaveCount(1);
    expect($kitchen->drivers)->toHaveCount(1);
    expect($driver->shops)->toHaveCount(1);
    expect($driver->kitchens)->toHaveCount(1);
});

test('admin can see all entities', function () {
    $admin = User::factory()->create();
    $adminRole = Role::where('slug', 'super_admin')->first();
    $admin->update(['primary_role_id' => $adminRole->id]);

    Shop::factory()->count(3)->create();
    Kitchen::factory()->count(2)->create();

    expect($admin->getManagedShops())->toHaveCount(3);
    expect($admin->getManagedKitchens())->toHaveCount(2);
});

test('shop manager only sees their shops', function () {
    $manager = User::factory()->create();
    $shopManagerRole = Role::where('slug', 'shop_manager')->first();
    $manager->update(['primary_role_id' => $shopManagerRole->id]);

    $myShop = Shop::factory()->create();
    $otherShop = Shop::factory()->create();

    $manager->shops()->attach($myShop->id);

    expect($manager->getManagedShops())->toHaveCount(1);
    expect($manager->managesShop($myShop->id))->toBeTrue();
    expect($manager->managesShop($otherShop->id))->toBeFalse();
});
