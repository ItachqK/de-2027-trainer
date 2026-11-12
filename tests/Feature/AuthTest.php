<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_is_logged_in(): void
    {
        $response = $this->post('/register', [
            'fio' => 'Петров Пётр Петрович',
            'login' => 'petrov2027',
            'email' => 'petrov@example.ru',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('app'));
        $this->assertAuthenticated();

        $user = User::where('login', 'petrov2027')->firstOrFail();
        $this->assertSame('student', $user->role);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_registration_validates_kim_rules(): void
    {
        $response = $this->from('/register')->post('/register', [
            'fio' => 'Petrov Petr',
            'login' => 'short',
            'email' => 'not-an-email',
            'password' => '123',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['fio', 'login', 'email', 'password']);
        $this->assertGuest();
    }

    public function test_user_can_login_with_login_and_password(): void
    {
        $user = User::factory()->create([
            'login' => 'student2027',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'login' => 'student2027',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('app'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_shows_error(): void
    {
        User::factory()->create([
            'login' => 'student2027',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'login' => 'student2027',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
