<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_renders(): void
    {
        $this->get('/register')->assertOk()->assertSee('Создание аккаунта');
    }

    public function test_user_can_register_and_is_logged_in(): void
    {
        $response = $this->post('/register', [
            'fio' => 'Петрова Анна Игоревна',
            'login' => 'anna2027',
            'phone' => '8(912)000-11-22',
            'email' => 'anna@example.ru',
            'password' => 'Student2027!',
            'password_confirmation' => 'Student2027!',
        ]);

        $response->assertRedirect(route('bookings.index'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'login' => 'anna2027',
            'role' => 'user',
        ]);
    }

    public function test_registration_rejects_duplicate_login(): void
    {
        User::create([
            'fio' => 'Иванов Иван Иванович',
            'login' => 'ivan2027',
            'phone' => '8(912)111-22-33',
            'email' => 'ivan@example.ru',
            'password' => 'Student2027!',
            'role' => 'user',
        ]);

        $this->post('/register', [
            'fio' => 'Петрова Анна Игоревна',
            'login' => 'ivan2027',
            'phone' => '8(912)000-11-22',
            'email' => 'anna@example.ru',
            'password' => 'Student2027!',
            'password_confirmation' => 'Student2027!',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_user_can_login_with_login_and_password(): void
    {
        User::create([
            'fio' => 'Иванов Иван Иванович',
            'login' => 'ivan2027',
            'phone' => '8(912)111-22-33',
            'email' => 'ivan@example.ru',
            'password' => 'Student2027!',
            'role' => 'user',
        ]);

        $this->post('/login', [
            'login' => 'ivan2027',
            'password' => 'Student2027!',
        ])->assertRedirect(route('bookings.index'));

        $this->assertAuthenticated();
    }

    public function test_user_cannot_open_admin_area(): void
    {
        $user = User::create([
            'fio' => 'Иванов Иван Иванович',
            'login' => 'ivan2027',
            'phone' => '8(912)111-22-33',
            'email' => 'ivan@example.ru',
            'password' => 'Student2027!',
            'role' => 'user',
        ]);

        $this->actingAs($user)->get('/admin/bookings')->assertForbidden();
    }

    public function test_admin_can_open_admin_area(): void
    {
        $admin = User::create([
            'fio' => 'Администратор портала',
            'login' => 'admin2027',
            'phone' => '8(343)200-18-14',
            'email' => 'admin@example.ru',
            'password' => 'Admin2027!',
            'role' => 'admin',
        ]);

        $this->actingAs($admin)->get('/admin/bookings')->assertOk()->assertSee('Заявки пользователей');
    }
}
