<x-filament-panels::page>
    <div class="space-y-6">
        {{-- User Info --}}
        <x-filament::section>
            <x-slot name="heading">
                User Information
            </x-slot>
            <x-slot name="description">
                Your account details and primary role
            </x-slot>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="text-sm font-medium text-gray-500">Name</span>
                    <p class="text-base">{{ $this->user->name }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Email</span>
                    <p class="text-base">{{ $this->user->email }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Primary Role</span>
                    <x-filament::badge color="{{ $this->user->primaryRole->color ?? 'gray' }}">
                        {{ $this->user->primaryRole->name }}
                    </x-filament::badge>
                </div>
            </div>
        </x-filament::section>

        {{-- Roles --}}
        <x-filament::section>
            <x-slot name="heading">
                My Roles
            </x-slot>
            <x-slot name="description">
                Roles assigned to you with their associated scopes
            </x-slot>

            <div class="space-y-3">
                @forelse ($this->roles as $role)
                    <div class="flex items-start justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <x-filament::badge color="{{ $role->color ?? 'gray' }}" class="mb-2">
                                {{ $role->name }}
                            </x-filament::badge>
                            @if ($role->pivot->scope_type)
                                <p class="text-sm text-gray-600">
                                    Scope: {{ ucfirst($role->pivot->scope_type) }}
                                    @if ($role->pivot->scope_id)
                                        (ID: {{ $role->pivot->scope_id }})
                                    @endif
                                </p>
                            @endif
                            @if ($role->pivot->valid_until)
                                <p class="text-xs text-gray-500 mt-1">
                                    Valid until: {{ $role->pivot->valid_until->format('Y-m-d H:i') }}
                                </p>
                            @endif
                        </div>
                        <span class="text-xs text-gray-500">
                            {{ $role->permissions->count() }} permissions
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No additional roles assigned</p>
                @endforelse
            </div>
        </x-filament::section>

        {{-- Direct Permissions --}}
        <x-filament::section>
            <x-slot name="heading">
                Direct Permissions
            </x-slot>
            <x-slot name="description">
                Permissions assigned to you directly (not through roles)
            </x-slot>

            <div class="grid grid-cols-2 gap-2">
                @forelse ($this->directPermissions as $permission)
                    <div class="flex items-center gap-2 text-sm">
                        <x-filament::badge color="success" size="xs">
                            ✓
                        </x-filament::badge>
                        {{ $permission->name }}
                    </div>
                @empty
                    <p class="text-sm text-gray-500 col-span-2">No direct permissions</p>
                @endforelse
            </div>
        </x-filament::section>

        {{-- Inherited Permissions --}}
        <x-filament::section>
            <x-slot name="heading">
                Permissions via Roles
            </x-slot>
            <x-slot name="description">
                All permissions you have through your assigned roles
            </x-slot>

            <div class="grid grid-cols-2 gap-2">
                @forelse ($this->inheritedPermissions as $permission)
                    <div class="flex items-center gap-2 text-sm">
                        <x-filament::badge color="info" size="xs">
                            🔹
                        </x-filament::badge>
                        {{ $permission->name }}
                    </div>
                @empty
                    <p class="text-sm text-gray-500 col-span-2">No inherited permissions</p>
                @endforelse
            </div>
        </x-filament::section>

        {{-- User Groups --}}
        @if ($this->groups->count() > 0)
            <x-filament::section>
                <x-slot name="heading">
                    My Groups
                </x-slot>
                <x-slot name="description">
                    Groups you belong to
                </x-slot>

                <div class="space-y-2">
                    @foreach ($this->groups as $group)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                            <span class="font-medium">{{ $group->name }}</span>
                            <span class="text-xs text-gray-500">
                                {{ $group->permissions->count() }} permissions
                            </span>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
