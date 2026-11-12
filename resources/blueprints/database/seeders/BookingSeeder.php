<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        if (Booking::exists()) {
            return;
        }

        $maria = User::where('login', 'maria2027')->first();
        $dmitry = User::where('login', 'dmitry2027')->first();

        if (! $maria || ! $dmitry) {
            return;
        }

        $titles = array_map('trim', explode(',', '@@OPTIONS@@'));
        $first = $titles[0] ?? '@@OBJECT@@';
        $second = $titles[1] ?? $first;
        $third = $titles[2] ?? $first;

        $done = Booking::create([
            'user_id' => $maria->id,
            'object_title' => $first,
            'event_date' => now()->subDays(9)->toDateString(),
            'payment' => 'sbp',
            'status' => 'done',
            'comment' => 'Нужен счёт на оплату.',
            'created_at' => now()->subDays(12),
            'updated_at' => now()->subDays(9),
        ]);

        Review::create([
            'booking_id' => $done->id,
            'user_id' => $maria->id,
            'rating' => 5,
            'text' => 'Заявку подтвердили в тот же день, всё прошло без задержек. Отдельно спасибо за напоминание накануне.',
        ]);

        Booking::create([
            'user_id' => $maria->id,
            'object_title' => $second,
            'event_date' => now()->addDays(2)->toDateString(),
            'payment' => 'onsite',
            'status' => 'assigned',
            'comment' => null,
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDay(),
        ]);

        Booking::create([
            'user_id' => $dmitry->id,
            'object_title' => $third,
            'event_date' => now()->addDays(5)->toDateString(),
            'payment' => 'onsite',
            'status' => 'new',
            'comment' => 'Уточните, пожалуйста, время.',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $doneTwo = Booking::create([
            'user_id' => $dmitry->id,
            'object_title' => $second,
            'event_date' => now()->subDays(20)->toDateString(),
            'payment' => 'onsite',
            'status' => 'done',
            'comment' => null,
            'created_at' => now()->subDays(24),
            'updated_at' => now()->subDays(20),
        ]);

        Review::create([
            'booking_id' => $doneTwo->id,
            'user_id' => $dmitry->id,
            'rating' => 4,
            'text' => 'Всё по делу: заявку приняли быстро, статус обновили вовремя. Хотелось бы больше вариантов на выбор.',
        ]);
    }
}
