<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateAdmin extends Command
{
    protected $signature = 'user:create-admin
        {--name= : Nama admin}
        {--email= : Email admin}
        {--password= : Kata sandi admin (minimal 12 karakter)}';

    protected $description = 'Membuat akun admin tanpa memakai seeder demo';

    public function handle(): int
    {
        $name = (string) ($this->option('name') ?: $this->ask('Nama admin'));
        $email = (string) ($this->option('email') ?: $this->ask('Email admin'));
        $password = (string) ($this->option('password') ?: $this->secret('Kata sandi (minimal 12 karakter)'));

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'string',
                'email',
                'max:190',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'password' => ['required', 'string', 'min:12'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'nama' => trim($name),
            'email' => strtolower(trim($email)),
            'password' => $password,
            'role' => UserRole::Admin,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->info("Admin {$user->email} berhasil dibuat.");

        return self::SUCCESS;
    }
}
