<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Received Delegations --}}
        <div>
            <h2 class="text-lg font-semibold mb-4">Delegations I Received</h2>
            {{ $this->table }}
        </div>

        {{-- Given Delegations --}}
        <div>
            <h2 class="text-lg font-semibold mb-4">Delegations I Gave</h2>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Delegated To</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Permission</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Scope</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valid Until</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($this->getGivenDelegations() as $delegation)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $delegation->delegatee->name }}</td>
                                <td class="px-6 py-4 text-sm">{{ $delegation->permission->name }}</td>
                                <td class="px-6 py-4 text-sm">{{ $delegation->scope?->name ?? 'Global' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($delegation->revoked_at)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Revoked</span>
                                    @elseif ($delegation->valid_until < now())
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Expired</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $delegation->valid_until->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No delegations given</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
