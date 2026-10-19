<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'email' => 'admin@arsip.test',
                'nama' => 'Administrator',
                'role' => UserRole::Admin,
            ],
            [
                'email' => 'staf@arsip.test',
                'nama' => 'Staf Arsip',
                'role' => UserRole::StafArsip,
            ],
        ];

        foreach ($accounts as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'nama' => $account['nama'],
                    'password' => 'password',
                    'role' => $account['role']->value,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
