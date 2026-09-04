<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    /**
     * Provision the single admin account from environment configuration.
     *
     * There is no self-registration in this application, so the one
     * authorized admin is created here (or updated, if it already exists)
     * from ADMIN_NAME / ADMIN_USERNAME / ADMIN_EMAIL / ADMIN_PASSWORD.
     * These must be set explicitly — we deliberately do not fall back to a
     * default password. ADMIN_USERNAME is the login ID; ADMIN_EMAIL only
     * receives password-reset links.
     */
    public function run(): void
    {
        $name = env('ADMIN_NAME');
        $username = env('ADMIN_USERNAME');
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $name || ! $username || ! $email || ! $password) {
            throw new RuntimeException(
                'Set ADMIN_NAME, ADMIN_USERNAME, ADMIN_EMAIL, and ADMIN_PASSWORD in your .env before seeding the admin user.'
            );
        }

        User::updateOrCreate(
            ['username' => $username],
            [
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        $this->call(IndustrySeeder::class);
    }
}
