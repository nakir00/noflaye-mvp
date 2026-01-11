<?php

namespace App\Console\Commands;

use App\Enums\Template;
use App\Models\PermissionTemplate;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:super-admin
                            {--name= : The name of the admin user}
                            {--email= : The email of the admin user}
                            {--password= : The password for the admin user}
                            {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a super administrator user with all permissions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔐 Creating Super Administrator...');
        $this->newLine();

        // Collect user information
        $name = $this->option('name') ?? $this->ask('Admin name', 'Super Admin');
        $email = $this->option('email') ?? $this->ask('Admin email', 'admin@noflaye.com');
        $password = $this->option('password') ?? $this->secret('Admin password (min 8 chars)');

        // Validate input
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            $this->error('❌ Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->error("  • {$error}");
            }
            return self::FAILURE;
        }

        // Show summary
        $this->newLine();
        $this->info('📋 Admin Details:');
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $name],
                ['Email', $email],
                ['Password', str_repeat('•', strlen($password))],
            ]
        );

        // Confirm creation
        if (! $this->option('force') && ! $this->confirm('Create this super admin?', true)) {
            $this->warn('❌ Operation cancelled');
            return self::FAILURE;
        }

        try {
            // Get or create Super Admin template
            $template = PermissionTemplate::firstOrCreate(
                ['slug' => Template::SUPER_ADMIN->value],
                [
                    'name' => 'Super Administrator',
                    'description' => 'Full system access with all permissions',
                    'is_active' => true,
                    'is_system' => true,
                ]
            );

            // Create user
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'primary_template_id' => $template->id,
            ]);

            // Assign template
            $user->templates()->attach($template->id, [
                'auto_sync' => true,
                'valid_from' => now(),
            ]);

            $this->newLine();
            $this->info('✅ Super admin created successfully!');
            $this->newLine();

            $this->info('🎉 Login credentials:');
            $this->line("   Email: {$email}");
            $this->line("   Password: {$password}");
            $this->newLine();

            $this->info('🔗 Access panels:');
            $this->line('   Admin Panel: ' . url('/admin'));
            $this->newLine();

            $this->warn('⚠️  Please change the password after first login!');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error creating super admin:');
            $this->error("   {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
