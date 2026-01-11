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
                    <span class="text-sm font-medium text-gray-500">Primary Template</span>
                    @if ($this->primaryTemplate)
                        <x-filament::badge color="success">
                            {{ $this->primaryTemplate->name }}
                        </x-filament::badge>
                    @else
                        <x-filament::badge color="gray">
                            No template assigned
                        </x-filament::badge>
                    @endif
                </div>
            </div>
        </x-filament::section>

        {{-- Templates --}}
        <x-filament::section>
            <x-slot name="heading">
                My Templates
            </x-slot>
            <x-slot name="description">
                Permission templates assigned to you
            </x-slot>

            <div class="space-y-3">
                @forelse ($this->templates as $template)
                    <div class="flex items-start justify-between p-4 bg-gray-50 rounded-lg dark:bg-gray-800">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <x-filament::badge color="primary">
                                    {{ $template->name }}
                                </x-filament::badge>
                                @if ($template->id === $this->user->primary_template_id)
                                    <x-filament::badge color="success" size="xs">
                                        Primary
                                    </x-filament::badge>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {{ $template->description }}
                            </p>
                            <div class="grid grid-cols-2 gap-2 text-xs text-gray-500">
                                @if ($template->pivot->scope_id)
                                    <div>
                                        <span class="font-medium">Scope ID:</span> {{ $template->pivot->scope_id }}
                                    </div>
                                @endif
                                <div>
                                    <span class="font-medium">Version:</span> {{ $template->pivot->template_version }}
                                </div>
                                <div>
                                    <span class="font-medium">Auto Upgrade:</span> {{ $template->pivot->auto_upgrade ? 'Yes' : 'No' }}
                                </div>
                                <div>
                                    <span class="font-medium">Auto Sync:</span> {{ $template->pivot->auto_sync ? 'Yes' : 'No' }}
                                </div>
                                @if ($template->pivot->valid_until)
                                    <div class="col-span-2">
                                        <span class="font-medium">Valid until:</span> {{ \Carbon\Carbon::parse($template->pivot->valid_until)->format('Y-m-d H:i') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-gray-500 mb-1">
                                {{ $template->permissions->count() }} permissions
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $template->wildcards->count() }} wildcards
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No templates assigned</p>
                @endforelse
            </div>
        </x-filament::section>

        {{-- Direct Permissions --}}
        <x-filament::section>
            <x-slot name="heading">
                Direct Permissions
            </x-slot>
            <x-slot name="description">
                Permissions assigned to you directly (not through templates)
            </x-slot>

            <div class="grid grid-cols-2 gap-2">
                @forelse ($this->directPermissions as $permission)
                    <div class="flex items-center gap-2 text-sm">
                        <x-filament::badge color="success" size="xs">
                            ✓
                        </x-filament::badge>
                        <span>{{ $permission->name }}</span>
                        @if ($permission->group)
                            <span class="text-xs text-gray-400">({{ $permission->group->name }})</span>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-500 col-span-2">No direct permissions</p>
                @endforelse
            </div>
        </x-filament::section>

        {{-- All Effective Permissions --}}
        <x-filament::section>
            <x-slot name="heading">
                All Effective Permissions
            </x-slot>
            <x-slot name="description">
                Complete list of all permissions you have (templates + direct + delegated)
            </x-slot>

            <div class="grid grid-cols-2 gap-2">
                @forelse ($this->effectivePermissions as $permission)
                    <div class="flex items-center gap-2 text-sm">
                        <x-filament::badge color="info" size="xs">
                            ✓
                        </x-filament::badge>
                        <span>{{ $permission->name }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 col-span-2">No permissions</p>
                @endforelse
            </div>
        </x-filament::section>

        {{-- Delegations --}}
        @if ($this->delegations->count() > 0)
            <x-filament::section>
                <x-slot name="heading">
                    Delegated Permissions
                </x-slot>
                <x-slot name="description">
                    Permissions that have been delegated to you by other users
                </x-slot>

                <div class="space-y-2">
                    @foreach ($this->delegations as $delegation)
                        <div class="flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/20 rounded">
                            <div>
                                <span class="font-medium">{{ $delegation->permission->name }}</span>
                                <p class="text-xs text-gray-500 mt-1">
                                    Delegated by: {{ $delegation->delegator->name }}
                                </p>
                                @if ($delegation->valid_until)
                                    <p class="text-xs text-gray-500">
                                        Valid until: {{ \Carbon\Carbon::parse($delegation->valid_until)->format('Y-m-d H:i') }}
                                    </p>
                                @endif
                            </div>
                            <x-filament::badge color="{{ $delegation->is_active ? 'success' : 'gray' }}" size="xs">
                                {{ $delegation->is_active ? 'Active' : 'Inactive' }}
                            </x-filament::badge>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
