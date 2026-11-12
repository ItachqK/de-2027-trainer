<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $login = 'maria2027', string $role = 'user'): User
    {
        return User::create([
            'fio' => $role === 'admin' ? 'Администратор портала' : 'Иванова Мария Сергеевна',
            'login' => $login,
            'phone' => '8(912)345-67-89',
            'email' => $login.'@example.ru',
            'password' => 'Student2027!',
            'role' => $role,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/bookings')->assertRedirect('/login');
    }

    public function test_user_can_create_booking(): void
    {
        $user = $this->user();

        $this->actingAs($user)->post('/bookings', [
            'object_title' => 'роман',
            'event_date' => now()->addDays(3)->toDateString(),
            'payment' => 'sbp',
            'comment' => 'Нужен счёт.',
        ])->assertRedirect(route('bookings.index'));

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'object_title' => 'роман',
            'payment' => 'sbp',
            'status' => 'new',
        ]);
    }

    public function test_booking_date_cannot_be_in_the_past(): void
    {
        $this->actingAs($this->user())->post('/bookings', [
            'object_title' => 'роман',
            'event_date' => now()->subDay()->toDateString(),
            'payment' => 'onsite',
        ])->assertSessionHasErrors('event_date');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_status_moves_only_forward(): void
    {
        $admin = $this->user('admin2027', 'admin');
        $booking = Booking::create([
            'user_id' => $this->user()->id,
            'object_title' => 'роман',
            'event_date' => now()->addDays(2)->toDateString(),
            'payment' => 'onsite',
            'status' => 'new',
        ]);

        // new → done запрещён: пропуск шага.
        $this->actingAs($admin)
            ->patch("/admin/bookings/{$booking->id}/status", ['status' => 'done'])
            ->assertSessionHasErrors('status');

        $this->assertSame('new', $booking->fresh()->status);

        $this->actingAs($admin)
            ->patch("/admin/bookings/{$booking->id}/status", ['status' => 'assigned'])
            ->assertSessionHasNoErrors();

        $this->assertSame('assigned', $booking->fresh()->status);
    }

    public function test_review_is_allowed_only_after_done_and_only_once(): void
    {
        $user = $this->user();
        $booking = Booking::create([
            'user_id' => $user->id,
            'object_title' => 'роман',
            'event_date' => now()->subDays(2)->toDateString(),
            'payment' => 'onsite',
            'status' => 'done',
        ]);

        $this->actingAs($user)->post("/bookings/{$booking->id}/review", [
            'rating' => 5,
            'text' => 'Всё прошло хорошо, спасибо за оперативность.',
        ])->assertRedirect(route('bookings.index'));

        $this->assertDatabaseCount('reviews', 1);

        $this->actingAs($user)->post("/bookings/{$booking->id}/review", [
            'rating' => 4,
            'text' => 'Повторный отзыв по этой же заявке.',
        ])->assertSessionHasErrors('text');

        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_review_is_forbidden_for_unfinished_booking(): void
    {
        $user = $this->user();
        $booking = Booking::create([
            'user_id' => $user->id,
            'object_title' => 'роман',
            'event_date' => now()->addDays(2)->toDateString(),
            'payment' => 'onsite',
            'status' => 'assigned',
        ]);

        $this->actingAs($user)->post("/bookings/{$booking->id}/review", [
            'rating' => 5,
            'text' => 'Отзыв раньше времени оставлять нельзя.',
        ])->assertForbidden();
    }
}
