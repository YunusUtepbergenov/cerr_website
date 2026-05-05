<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class MakeAdmin extends Command
{
    protected $signature = 'user:make-admin {email} {--create : Create the user if it does not exist} {--name= : Name to use when creating} {--password= : Password to use when creating}';

    protected $description = 'Promote a user account to the admin role (optionally creating it).';

    public function handle(): int
    {
        $email = (string) $this->argument('email');

        $user = User::where('email', $email)->first();

        if (! $user) {
            if (! $this->option('create')) {
                $this->error("No user found with email: {$email}");
                $this->line('Pass --create to create the user and promote in one step, e.g.');
                $this->line("  php artisan user:make-admin {$email} --create");

                return self::FAILURE;
            }

            $name = (string) ($this->option('name') ?: text('Name', required: true));
            $plainPassword = (string) ($this->option('password') ?: password('Password', required: true));

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($plainPassword),
                'email_verified_at' => now(),
                'role' => 'admin',
            ]);

            $this->info("Created admin user {$user->name} <{$user->email}>.");

            return self::SUCCESS;
        }

        $user->role = 'admin';
        $user->save();

        $this->info("User {$user->name} <{$user->email}> is now an admin.");

        return self::SUCCESS;
    }
}
