<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'username' => 'user_test',
            'fullName' => 'User Test',
            'email' => 'user_test@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ], $overrides));
    }

    public function test_login_page_can_be_opened(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Đăng nhập');
    }

    public function test_user_can_login_with_valid_username_and_password(): void
    {
        $user = $this->makeUser([
            'username' => 'ngoc_test',
            'email' => 'ngoc_test@example.com',
        ]);

        $response = $this->post('/login', [
            'username' => 'ngoc_test',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $this->makeUser([
            'username' => 'wrong_pass_user',
            'email' => 'wrong_pass_user@example.com',
        ]);

        $response = $this->from('/login')->post('/login', [
            'username' => 'wrong_pass_user',
            'password' => 'sai_mat_khau',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_username_and_password_are_required_when_login(): void
    {
        $response = $this->from('/login')->post('/login', [
            'username' => '',
            'password' => '',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['username', 'password']);
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = $this->makeUser([
            'username' => 'logout_user',
            'email' => 'logout_user@example.com',
        ]);

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
