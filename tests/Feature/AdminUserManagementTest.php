<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_create_form(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/users/create')
            ->assertOk()
            ->assertSee('Tambah Pengguna');
    }

    public function test_admin_can_open_edit_form(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $user))
            ->assertOk()
            ->assertSee('Edit Pengguna')
            ->assertSee($user->email);
    }

    public function test_admin_can_create_staf_user(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/admin/users', [
                'nama' => 'Staf Baru',
                'email' => 'Staf.Baru@Example.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => UserRole::StafArsip->value,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'staf.baru@example.test')->first();

        $this->assertNotNull($user);
        $this->assertSame(UserRole::StafArsip, $user->role);
        $this->assertTrue($user->is_active);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'CREATE_USER',
            'entity_id' => $user->id,
        ]);
    }

    public function test_email_must_be_unique_case_insensitively(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['email' => 'sama@example.test']);

        $this->actingAs($admin)
            ->post('/admin/users', [
                'nama' => 'Duplikat',
                'email' => 'SAMA@example.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => UserRole::StafArsip->value,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_staf_cannot_create_users(): void
    {
        $staf = User::factory()->create();

        $this->actingAs($staf)
            ->post('/admin/users', [
                'nama' => 'Tidak Boleh',
                'email' => 'tidak@example.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => UserRole::StafArsip->value,
                'is_active' => '1',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_update_user_without_changing_password(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create([
            'nama' => 'Nama Lama',
            'email' => 'lama@example.test',
        ]);
        $originalPassword = $user->password;

        $this->actingAs($admin)
            ->put(route('admin.users.update', $user), [
                'nama' => 'Nama Baru',
                'email' => 'baru@example.test',
                'password' => '',
                'password_confirmation' => '',
                'role' => UserRole::StafArsip->value,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.users.index'));

        $user->refresh();

        $this->assertSame('Nama Baru', $user->nama);
        $this->assertSame('baru@example.test', $user->email);
        $this->assertSame($originalPassword, $user->password);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'UPDATE_USER',
            'entity_id' => $user->id,
        ]);
    }

    public function test_admin_cannot_demote_self(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.users.update', $admin), [
                'nama' => $admin->nama,
                'email' => $admin->email,
                'password' => '',
                'password_confirmation' => '',
                'role' => UserRole::StafArsip->value,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('role');

        $this->assertSame(UserRole::Admin, $admin->fresh()->role);
    }

    public function test_admin_cannot_deactivate_self(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->patch(route('admin.users.toggle-active', $admin))
            ->assertSessionHasErrors('user');

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_admin_can_deactivate_another_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($admin)
            ->patch(route('admin.users.toggle-active', $user))
            ->assertRedirect();

        $this->assertFalse($user->fresh()->is_active);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'DEACTIVATE_USER',
            'entity_id' => $user->id,
        ]);
    }
}
