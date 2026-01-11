<?php

namespace App\Console\Commands\Permissions;

use App\Enums\Permission as PermissionEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use ReflectionClass;
use ReflectionMethod;

/**
 * Artisan Command: Check Policies
 *
 * This command validates the consistency between policies, permissions,
 * and models. It ensures all policies are properly registered, all
 * permissions are used, and identifies missing or orphaned policies.
 *
 * Features:
 * - Discovers all policies in the application
 * - Checks policy registration with Gate
 * - Validates permission usage in policies
 * - Identifies missing policies for models
 * - Detects unused permissions
 * - Provides detailed report with recommendations
 *
 * Usage:
 * ```bash
 * php artisan permissions:check-policies
 * php artisan permissions:check-policies --detailed
 * php artisan permissions:check-policies --fix
 * ```
 */
class CheckPolicies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:check-policies
                            {--detailed : Display detailed information}
                            {--fix : Suggest fixes for found issues}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and validate policy registration and permission usage';

    /**
     * Statistics for the check
     */
    protected array $stats = [
        'total_policies' => 0,
        'registered_policies' => 0,
        'unregistered_policies' => 0,
        'total_models' => 0,
        'models_with_policies' => 0,
        'models_without_policies' => 0,
        'total_permissions' => 0,
        'used_permissions' => 0,
        'unused_permissions' => 0,
        'policy_methods' => 0,
    ];

    /**
     * Issues found during check
     */
    protected array $issues = [];

    /**
     * Execute the console command.
     *
     * @return int Command exit code (0 for success)
     */
    public function handle(): int
    {
        $this->info('🔍 Checking policies and permissions...');
        $this->newLine();

        try {
            // 1. Discover all policies
            $policies = $this->discoverPolicies();
            $this->stats['total_policies'] = count($policies);

            // 2. Check policy registration
            $this->checkPolicyRegistration($policies);

            // 3. Discover models
            $models = $this->discoverModels();
            $this->stats['total_models'] = count($models);

            // 4. Check models have policies
            $this->checkModelsHavePolicies($models, $policies);

            // 5. Check permission usage
            $this->checkPermissionUsage($policies);

            // 6. Display results
            $this->displayResults();

            // 7. Suggest fixes if requested
            if ($this->option('fix')) {
                $this->suggestFixes();
            }

            return empty($this->issues) ? self::SUCCESS : self::FAILURE;
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            $this->error($e->getTraceAsString());

            return self::FAILURE;
        }
    }

    /**
     * Discover all policy classes
     *
     * @return array Array of policy class names
     */
    protected function discoverPolicies(): array
    {
        $policyPath = app_path('Policies');
        $policies = [];

        if (! File::exists($policyPath)) {
            return $policies;
        }

        $files = File::allFiles($policyPath);

        foreach ($files as $file) {
            $className = 'App\\Policies\\'.str_replace(
                ['/', '.php'],
                ['\\', ''],
                $file->getRelativePathname()
            );

            if (class_exists($className)) {
                $policies[] = $className;
            }
        }

        return $policies;
    }

    /**
     * Check if policies are properly registered with Gate
     *
     * @param  array  $policies  Array of policy class names
     */
    protected function checkPolicyRegistration(array $policies): void
    {
        $this->info('Checking policy registration...');

        foreach ($policies as $policyClass) {
            $modelClass = $this->getModelForPolicy($policyClass);

            if (! $modelClass) {
                $this->issues[] = [
                    'type' => 'warning',
                    'message' => "Cannot determine model for policy: {$policyClass}",
                ];

                continue;
            }

            $registeredPolicy = Gate::getPolicyFor($modelClass);

            if ($registeredPolicy && get_class($registeredPolicy) === $policyClass) {
                $this->stats['registered_policies']++;

                if ($this->option('detailed')) {
                    $this->line("  ✓ {$policyClass} → {$modelClass}");
                }
            } else {
                $this->stats['unregistered_policies']++;
                $this->issues[] = [
                    'type' => 'error',
                    'message' => "Policy not registered: {$policyClass}",
                    'fix' => 'Laravel should auto-discover this. Check naming convention.',
                ];
            }
        }

        $this->newLine();
    }

    /**
     * Get model class name for a policy
     *
     * @param  string  $policyClass  The policy class name
     * @return string|null The model class name
     */
    protected function getModelForPolicy(string $policyClass): ?string
    {
        // Extract model name from policy name (e.g., ShopPolicy → Shop)
        $policyName = class_basename($policyClass);
        $modelName = str_replace('Policy', '', $policyName);
        $modelClass = "App\\Models\\{$modelName}";

        return class_exists($modelClass) ? $modelClass : null;
    }

    /**
     * Discover all models
     *
     * @return array Array of model class names
     */
    protected function discoverModels(): array
    {
        $modelPath = app_path('Models');
        $models = [];

        if (! File::exists($modelPath)) {
            return $models;
        }

        $files = File::allFiles($modelPath);

        foreach ($files as $file) {
            $className = 'App\\Models\\'.str_replace(
                ['/', '.php'],
                ['\\', ''],
                $file->getRelativePathname()
            );

            if (class_exists($className)) {
                $models[] = $className;
            }
        }

        return $models;
    }

    /**
     * Check if models have corresponding policies
     *
     * @param  array  $models  Array of model class names
     * @param  array  $policies  Array of policy class names
     */
    protected function checkModelsHavePolicies(array $models, array $policies): void
    {
        $this->info('Checking models have policies...');

        // Models that should have policies (excluding system models)
        $excludedModels = [
            'App\\Models\\Permission',
            'App\\Models\\PermissionGroup',
            'App\\Models\\PermissionTemplate',
            'App\\Models\\UserGroup',
        ];

        foreach ($models as $modelClass) {
            if (in_array($modelClass, $excludedModels)) {
                continue;
            }

            $policy = Gate::getPolicyFor($modelClass);

            if ($policy) {
                $this->stats['models_with_policies']++;

                if ($this->option('detailed')) {
                    $this->line("  ✓ {$modelClass} → ".get_class($policy));
                }
            } else {
                $this->stats['models_without_policies']++;
                $this->issues[] = [
                    'type' => 'warning',
                    'message' => "Model has no policy: {$modelClass}",
                    'fix' => 'Consider creating: App\\Policies\\'.class_basename($modelClass).'Policy',
                ];
            }
        }

        $this->newLine();
    }

    /**
     * Check permission usage in policies
     *
     * @param  array  $policies  Array of policy class names
     */
    protected function checkPermissionUsage(array $policies): void
    {
        $this->info('Checking permission usage...');

        $allPermissions = collect(PermissionEnum::cases())->pluck('value')->toArray();
        $this->stats['total_permissions'] = count($allPermissions);

        $usedPermissions = [];

        foreach ($policies as $policyClass) {
            $reflection = new ReflectionClass($policyClass);
            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                // Skip constructor and trait methods
                if ($method->getName() === '__construct' || $method->getDeclaringClass()->getName() !== $policyClass) {
                    continue;
                }

                $this->stats['policy_methods']++;

                // Get method source code
                $source = $this->getMethodSource($method);

                // Find Permission enum usages
                preg_match_all('/Permission::([A-Z_]+)/', $source, $matches);

                foreach ($matches[1] as $permissionName) {
                    $permissionCase = PermissionEnum::tryFrom(
                        strtolower(str_replace('_', '.', $permissionName))
                    );

                    if ($permissionCase) {
                        $usedPermissions[] = $permissionCase->value;
                    }
                }
            }
        }

        $usedPermissions = array_unique($usedPermissions);
        $this->stats['used_permissions'] = count($usedPermissions);

        $unusedPermissions = array_diff($allPermissions, $usedPermissions);
        $this->stats['unused_permissions'] = count($unusedPermissions);

        if (! empty($unusedPermissions) && $this->option('detailed')) {
            $this->warn('Unused permissions found:');
            foreach ($unusedPermissions as $permission) {
                $this->line("  ⚠ {$permission}");
            }
            $this->newLine();
        }
    }

    /**
     * Get method source code
     *
     * @param  ReflectionMethod  $method  The method reflection
     * @return string The method source code
     */
    protected function getMethodSource(ReflectionMethod $method): string
    {
        $filename = $method->getFileName();
        $startLine = $method->getStartLine() - 1;
        $endLine = $method->getEndLine();
        $length = $endLine - $startLine;

        $source = file($filename);

        return implode('', array_slice($source, $startLine, $length));
    }

    /**
     * Display check results
     */
    protected function displayResults(): void
    {
        $this->components->info('✅ Policy check completed');
        $this->newLine();

        // Display statistics
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Policies', $this->stats['total_policies']],
                ['Registered Policies', $this->stats['registered_policies']],
                ['Unregistered Policies', $this->stats['unregistered_policies']],
                ['---', '---'],
                ['Total Models', $this->stats['total_models']],
                ['Models with Policies', $this->stats['models_with_policies']],
                ['Models without Policies', $this->stats['models_without_policies']],
                ['---', '---'],
                ['Total Permissions', $this->stats['total_permissions']],
                ['Used in Policies', $this->stats['used_permissions']],
                ['Unused', $this->stats['unused_permissions']],
                ['Policy Methods', $this->stats['policy_methods']],
            ]
        );

        // Display issues
        if (! empty($this->issues)) {
            $this->newLine();
            $this->warn('Issues found:');
            $this->newLine();

            foreach ($this->issues as $issue) {
                $icon = $issue['type'] === 'error' ? '❌' : '⚠️';
                $this->line("{$icon} {$issue['message']}");
            }
        } else {
            $this->newLine();
            $this->info('🎉 No issues found!');
        }
    }

    /**
     * Suggest fixes for found issues
     */
    protected function suggestFixes(): void
    {
        if (empty($this->issues)) {
            return;
        }

        $this->newLine();
        $this->info('💡 Suggested fixes:');
        $this->newLine();

        foreach ($this->issues as $issue) {
            if (isset($issue['fix'])) {
                $this->line("→ {$issue['fix']}");
            }
        }
    }
}
