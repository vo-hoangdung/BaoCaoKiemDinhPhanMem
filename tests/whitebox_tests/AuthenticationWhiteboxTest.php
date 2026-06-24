<?php

namespace Tests\WhiteboxTests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesClassroomData;
use Tests\TestCase;

/**
 * White-box tests cho chức năng đăng nhập/đăng xuất.
 *
 * Tương tự test_login.py của repo mẫu: kiểm tra validation, nhánh đăng nhập
 * thành công/thất bại, middleware auth và trạng thái session.
 */
class AuthenticationWhiteboxTest extends TestCase
{
    use CreatesClassroomData, RefreshDatabase;

    public function test_01_login_page_and_required_fields(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Đăng nhập');

        $this->post('/login', [])
            ->assertSessionHasErrors(['username', 'password']);
    }

    public function test_02_login_success_sets_authenticated_session(): void
    {
        $this->createAdmin(['username' => 'admin']);

        $this->post('/login', [
            'username' => 'admin',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
    }

    public function test_03_login_rejects_wrong_password_and_unknown_username(): void
    {
        $this->createAdmin(['username' => 'admin']);

        $this->from('/login')->post('/login', [
            'username' => 'admin',
            'password' => 'wrong-password',
        ])->assertRedirect('/login')->assertSessionHasErrors('username');

        $this->assertGuest();

        $this->from('/login')->post('/login', [
            'username' => 'ghost',
            'password' => 'password',
        ])->assertRedirect('/login')->assertSessionHasErrors('username');
    }

    public function test_04_dashboard_requires_authentication_and_logout_clears_session(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');

        $admin = $this->createAdmin();
        $this->actingAs($admin)->get('/dashboard')->assertOk();

        $this->actingAs($admin)->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }
}
