<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Support\CreatesClassroomData;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use CreatesClassroomData, RefreshDatabase;

    public function test_admin_can_create_user(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post('/users', [
            'username' => 'newteacher',
            'fullName' => 'Giảng viên mới',
            'email' => 'newteacher@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
        ])->assertRedirect('/dashboard')->assertSessionHas('success');

        $user = \App\Models\User::where('username', 'newteacher')->firstOrFail();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_duplicate_username_and_email_are_rejected(): void
    {
        $admin = $this->createAdmin();
        $existing = $this->createUser(['username' => 'duplicate', 'email' => 'duplicate@example.com']);

        $this->actingAs($admin)->post('/users', [
            'username' => $existing->username,
            'fullName' => 'Tên trùng',
            'email' => $existing->email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
        ])->assertSessionHasErrors(['username', 'email']);
    }

    public function test_password_confirmation_is_required(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post('/users', [
            'username' => 'mismatch',
            'fullName' => 'Sai xác nhận',
            'email' => 'mismatch@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
            'role' => 'user',
        ])->assertSessionHasErrors('password');
    }

    public function test_admin_can_update_user_without_changing_password(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $oldPassword = $user->password;

        $this->actingAs($admin)->put("/users/{$user->id}", [
            'username' => $user->username,
            'fullName' => 'Tên đã cập nhật',
            'email' => $user->email,
            'role' => 'admin',
        ])->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('Tên đã cập nhật', $user->fullName);
        $this->assertSame($oldPassword, $user->password);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->delete("/users/{$admin->id}")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_delete_other_user(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $this->actingAs($admin)->delete("/users/{$user->id}")
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_regular_user_cannot_manage_users(): void
    {
        $user = $this->createUser();
        $this->actingAs($user)->post('/users', [])->assertForbidden();
    }
}
