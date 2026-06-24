<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesClassroomData;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use CreatesClassroomData, RefreshDatabase;

    public function test_login_page_is_available(): void
    {
        $this->get('/login')->assertOk()->assertSee('Đăng nhập');
    }

    public function test_valid_credentials_open_dashboard(): void
    {
        $this->createAdmin(['username' => 'admin']);

        $this->post('/login', ['username' => 'admin', 'password' => 'password'])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticated();
    }

    public function test_invalid_password_returns_validation_error(): void
    {
        $this->createAdmin(['username' => 'admin']);

        $this->from('/login')
            ->post('/login', ['username' => 'admin', 'password' => 'wrong-password'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_missing_username_and_password_are_rejected(): void
    {
        $this->post('/login', [])
            ->assertSessionHasErrors(['username', 'password']);
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_logout_invalidates_authenticated_session(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }
}
