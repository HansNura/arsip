<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_an_active_admin_with_hashed_password(): void
    {
        $this->artisan('user:create-admin', [
            '--name' => 'Admin Baru',
            '--email' => 'Admin.Baru@Example.Test',
            '--password' => 'kata-sandi-panjang-123',
        ])->assertExitCode(0);

        $user = User::where('email', 'admin.baru@example.test')->firstOrFail();

        $this->assertSame('Admin Baru', $user->nama);
        $this->assertSame(UserRole::Admin, $user->role);
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('kata-sandi-panjang-123', $user->password));
    }

    public function test_command_rejects_short_password_and_duplicate_email(): void
    {
        User::factory()->create(['email' => 'sudah-ada@example.test']);

        $this->artisan('user:create-admin', [
            '--name' => 'Admin Baru',
            '--email' => 'sudah-ada@example.test',
            '--password' => 'pendek',
        ])->assertExitCode(1);

        $this->assertDatabaseCount('users', 1);
    }
}
