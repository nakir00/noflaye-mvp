<?php

namespace App\Filament\Widgets;

use App\Models\UserPermission;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

/**
 * PermissionGrowthChart
 *
 * Line chart showing permission assignments over time
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionGrowthChart extends ChartWidget
{
    protected ?string $heading = 'Permission Assignments Growth';

    protected static ?int $sort = 2;

    /**
     * Get the chart type
     */
    protected function getType(): string
    {
        return 'line';
    }

    /**
     * Get the chart data
     *
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $data = UserPermission::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Permissions Assigned',
                    'data' => $data->pluck('count')->toArray(),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $data->pluck('date')->map(fn ($date) => \Carbon\Carbon::parse($date)->format('M d'))->toArray(),
        ];
    }
}
