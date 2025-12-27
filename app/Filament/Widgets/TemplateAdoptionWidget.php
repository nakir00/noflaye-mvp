<?php

namespace App\Filament\Widgets;

use App\Models\PermissionTemplate;
use Filament\Widgets\ChartWidget;

/**
 * TemplateAdoptionWidget
 *
 * Pie chart showing template usage distribution
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class TemplateAdoptionWidget extends ChartWidget
{
    protected static ?string $heading = 'Template Adoption';

    protected static ?int $sort = 4;

    /**
     * Get the chart type
     */
    protected function getType(): string
    {
        return 'pie';
    }

    /**
     * Get the chart data
     *
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $templates = PermissionTemplate::withCount('users')
            ->orderBy('users_count', 'desc')
            ->limit(5)
            ->get();

        $colors = [
            '#3b82f6', // blue
            '#10b981', // green
            '#f59e0b', // amber
            '#ef4444', // red
            '#8b5cf6', // purple
        ];

        return [
            'datasets' => [
                [
                    'data' => $templates->pluck('users_count')->toArray(),
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $templates->pluck('name')->toArray(),
        ];
    }
}
