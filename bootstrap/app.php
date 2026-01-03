<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        // Register middleware aliases
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Backup quotidien à 2h du matin
        $schedule->command('backup:run')
            ->daily()
            ->at('02:00')
            ->monitorName('daily-backup');

        // Cleanup des vieux backups à 3h du matin
        $schedule->command('backup:clean')
            ->daily()
            ->at('03:00')
            ->monitorName('backup-cleanup');

        // Monitor backups à 4h du matin
        $schedule->command('backup:monitor')
            ->daily()
            ->at('04:00')
            ->monitorName('backup-monitor');

        // Health check history cleanup
        $schedule->command('model:prune', [
            '--model' => [\Spatie\Health\Models\HealthCheckResultHistoryItem::class],
        ])
            ->daily()
            ->monitorName('health-cleanup');

        // Sync schedule monitor
        $schedule->command('schedule-monitor:sync')
            ->daily()
            ->monitorName('schedule-sync');

        // Clean old monitored tasks
        $schedule->command('schedule-monitor:clean')
            ->daily()
            ->monitorName('schedule-monitor-cleanup');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
