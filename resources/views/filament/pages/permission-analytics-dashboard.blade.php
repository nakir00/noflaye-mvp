<x-filament-panels::page>
    {{-- Header Widgets (Stats) --}}
    @if ($this->hasHeaderWidgets())
        <x-filament-widgets::widgets
            :columns="$this->getHeaderWidgetsColumns()"
            :widgets="$this->getHeaderWidgets()"
        />
    @endif

    {{-- Main Content Widgets (Charts + Tables) --}}
    <x-filament-widgets::widgets
        :columns="$this->getWidgetsColumns()"
        :widgets="$this->getWidgets()"
    />
</x-filament-panels::page>
