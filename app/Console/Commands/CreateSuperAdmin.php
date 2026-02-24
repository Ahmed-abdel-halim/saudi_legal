<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:superadmin {email?} {--password=} {--name=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a super admin user with full platform access';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('--- Radiif Super Admin Creation ---');

        $email = $this->argument('email') ?? $this->ask('Admin Email Address');
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email format.');
            return 1;
        }

        if (User::where('email', $email)->exists()) {
            $this->error("A user with email {$email} already exists.");
            return 1;
        }

        $name = $this->option('name') ?? $this->ask('Admin Full Name', 'System Admin');
        
        $password = $this->option('password');
        if (!$password) {
            $password = $this->secret('Enter Password (minimum 8 characters)');
            $confirmPassword = $this->secret('Confirm Password');

            if ($password !== $confirmPassword) {
                $this->error('Passwords do not match.');
                return 1;
            }

            if (strlen($password) < 8) {
                $this->error('Password must be at least 8 characters long.');
                return 1;
            }
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        $this->info('Super Admin created successfully!');
        $this->line("Email: {$email}");
        $this->line("Role: {$user->role}");
        return 0;
    }
}
