<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Delegator</h4>
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $record->delegator->name }}</p>
            <p class="text-xs text-gray-500">{{ $record->delegator->email }}</p>
        </div>

        <div>
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Delegatee</h4>
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $record->delegatee->name }}</p>
            <p class="text-xs text-gray-500">{{ $record->delegatee->email }}</p>
        </div>

        <div>
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Permission</h4>
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $record->permission->name }}</p>
            <p class="text-xs text-gray-500">{{ $record->permission->slug }}</p>
        </div>

        <div>
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Scope</h4>
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $record->scope?->name ?? 'Global' }}</p>
        </div>

        <div>
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Valid From</h4>
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $record->valid_from->format('Y-m-d H:i') }}</p>
        </div>

        <div>
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Valid Until</h4>
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $record->valid_until->format('Y-m-d H:i') }}</p>
        </div>

        <div>
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Can Re-delegate</h4>
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $record->can_redelegate ? 'Yes' : 'No' }}</p>
        </div>

        <div>
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Max Re-delegation Depth</h4>
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $record->max_redelegation_depth }}</p>
        </div>

        @if ($record->revoked_at)
            <div class="col-span-2">
                <h4 class="text-sm font-semibold text-red-700 dark:text-red-400">Revoked At</h4>
                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $record->revoked_at->format('Y-m-d H:i') }}</p>
                @if ($record->revoked_by)
                    <p class="text-xs text-gray-500">By: {{ $record->revokedBy->name }}</p>
                @endif
            </div>

            @if ($record->revocation_reason)
                <div class="col-span-2">
                    <h4 class="text-sm font-semibold text-red-700 dark:text-red-400">Revocation Reason</h4>
                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $record->revocation_reason }}</p>
                </div>
            @endif
        @endif

        @if ($record->reason)
            <div class="col-span-2">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Delegation Reason</h4>
                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $record->reason }}</p>
            </div>
        @endif
    </div>

    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between text-xs text-gray-500">
            <span>Created: {{ $record->created_at->format('Y-m-d H:i') }}</span>
            <span>Updated: {{ $record->updated_at->format('Y-m-d H:i') }}</span>
        </div>
    </div>
</div>
