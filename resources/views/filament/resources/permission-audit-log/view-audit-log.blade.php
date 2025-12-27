<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Action</h4>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                @if($record->action === 'granted') bg-green-100 text-green-800
                @elseif($record->action === 'revoked') bg-red-100 text-red-800
                @elseif($record->action === 'updated') bg-blue-100 text-blue-800
                @elseif($record->action === 'expired') bg-yellow-100 text-yellow-800
                @elseif($record->action === 'delegated') bg-purple-100 text-purple-800
                @else bg-gray-100 text-gray-800
                @endif">
                {{ ucfirst($record->action) }}
            </span>
        </div>

        <div>
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Source</h4>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                @if($record->source === 'direct') bg-blue-100 text-blue-800
                @elseif($record->source === 'template') bg-green-100 text-green-800
                @elseif($record->source === 'delegation') bg-purple-100 text-purple-800
                @elseif($record->source === 'wildcard') bg-yellow-100 text-yellow-800
                @else bg-gray-100 text-gray-800
                @endif">
                {{ ucfirst($record->source) }}
            </span>
        </div>

        <div>
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">User</h4>
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $record->user_name }}</p>
            <p class="text-xs text-gray-500">{{ $record->user_email }}</p>
        </div>

        <div>
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Performed By</h4>
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $record->performed_by_name }}</p>
        </div>

        <div>
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Permission</h4>
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $record->permission_name }}</p>
            <p class="text-xs text-gray-500">{{ $record->permission_slug }}</p>
        </div>

        @if($record->source_name)
            <div>
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Source Name</h4>
                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $record->source_name }}</p>
            </div>
        @endif

        @if($record->ip_address)
            <div>
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">IP Address</h4>
                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $record->ip_address }}</p>
            </div>
        @endif

        @if($record->user_agent)
            <div>
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">User Agent</h4>
                <p class="text-sm text-gray-900 dark:text-gray-100 truncate" title="{{ $record->user_agent }}">
                    {{ $record->user_agent }}
                </p>
            </div>
        @endif

        @if($record->reason)
            <div class="col-span-2">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Reason</h4>
                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $record->reason }}</p>
            </div>
        @endif

        @if($record->metadata)
            <div class="col-span-2">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Metadata</h4>
                <pre class="mt-2 text-xs bg-gray-50 dark:bg-gray-900 p-3 rounded-lg overflow-auto">{{ json_encode($record->metadata, JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif
    </div>

    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between text-xs text-gray-500">
            <span>Date: {{ $record->created_at->format('Y-m-d H:i:s') }}</span>
            <span>ID: {{ $record->id }}</span>
        </div>
    </div>
</div>
