<?php

namespace App\Providers;

use App\Services\Permissions\ConditionEvaluator;
use App\Services\Permissions\PermissionAnalytics;
use App\Services\Permissions\PermissionApprovalWorkflow;
use App\Services\Permissions\PermissionAuditLogger;
use App\Services\Permissions\PermissionChecker;
use App\Services\Permissions\PermissionDelegator;
use App\Services\Permissions\ScopeManager;
use App\Services\Permissions\TemplateVersionManager;
use App\Services\Permissions\WildcardExpander;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register permission services as singletons
        $this->app->singleton(ScopeManager::class);
        $this->app->singleton(WildcardExpander::class);
        $this->app->singleton(ConditionEvaluator::class);
        $this->app->singleton(PermissionChecker::class);
        $this->app->singleton(PermissionAuditLogger::class);
        $this->app->singleton(PermissionDelegator::class);
        $this->app->singleton(TemplateVersionManager::class);
        $this->app->singleton(PermissionAnalytics::class);
        $this->app->singleton(PermissionApprovalWorkflow::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Boot observers
        $this->bootObservers();
    }

    /**
     * Boot model observers
     */
    private function bootObservers(): void
    {
        \App\Models\UserGroup::observe(\App\Observers\UserGroupObserver::class);
        \App\Models\PermissionTemplate::observe(\App\Observers\PermissionTemplateObserver::class);
        \App\Models\PermissionGroup::observe(\App\Observers\PermissionGroupObserver::class);
        \App\Models\Permission::observe(\App\Observers\PermissionObserver::class);
        \App\Models\User::observe(\App\Observers\UserPermissionObserver::class);
        \App\Models\User::observe(\App\Observers\UserObserver::class);
    }
}
