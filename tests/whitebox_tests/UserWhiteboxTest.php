<?php

namespace Tests\WhiteboxTests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Support\CreatesClassroomData;
use Tests\TestCase;

/**
 * White-box tests cho quản lý người dùng và role.
 */
class UserWhiteboxTest extends TestCase
{
    use CreatesClassroomData, RefreshDatabase;

    public function test_01_user_model_role_helper_and_password_hash(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($user->isAdmin());
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_02_admin_can_create_user_and_validation_rejects_duplicates_or_password_mismatch(): void
    {
        $admin = $this->createAdmin();
        $existing = $this->createUser(['username' => 'duplicate', 'email' => 'duplicate@example.com']);

        $this->actingAs($admin)->post('/users', [
            'username' => 'whitebox_user',
            'fullName' => 'Người dùng white-box',
            'email' => 'whitebox@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
        ])->assertRedirect('/dashboard')->assertSessionHas('success');

        $created = User::where('username', 'whitebox_user')->firstOrFail();
        $this->assertTrue(Hash::check('password123', $created->password));

        $this->actingAs($admin)->post('/users', [
            'username' => $existing->username,
            'fullName' => 'Tên trùng',
            'email' => $existing->email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
        ])->assertSessionHasErrors(['username', 'email']);

        $this->actingAs($admin)->post('/users', [
            'username' => 'mismatch',
            'fullName' => 'Sai xác nhận',
            'email' => 'mismatch@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
            'role' => 'user',
        ])->assertSessionHasErrors('password');
    }

    public function test_03_admin_can_update_user_without_password_and_cannot_delete_self(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $oldPassword = $user->password;

        $this->actingAs($admin)->put("/users/{$user->id}", [
            'username' => $user->username,
            'fullName' => 'Tên white-box đã sửa',
            'email' => $user->email,
            'role' => 'admin',
        ])->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('Tên white-box đã sửa', $user->fullName);
        $this->assertSame($oldPassword, $user->password);

        $this->actingAs($admin)->delete("/users/{$admin->id}")
            ->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_04_admin_can_delete_other_user_and_regular_user_cannot_manage_users(): void
    {
        $admin = $this->createAdmin();
        $target = $this->createUser();

        $this->actingAs($admin)->delete("/users/{$target->id}")
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $target->id]);

        $this->actingAs($this->createUser())->post('/users', [])
            ->assertForbidden();
    }
}
