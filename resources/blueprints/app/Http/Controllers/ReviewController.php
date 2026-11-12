<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id, 403);
        abort_unless($booking->isDone(), 403, 'Отзыв можно оставить после завершения заявки.');

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'text' => ['required', 'string', 'min:10', 'max:600'],
        ], [
            'rating.between' => 'Оценка — от 1 до 5.',
            'text.min' => 'Отзыв короче 10 символов.',
            'text.max' => 'Отзыв длиннее 600 символов.',
        ]);

        if ($booking->review()->exists()) {
            return back()->withErrors(['text' => 'Отзыв по этой заявке уже оставлен.']);
        }

        $booking->review()->create([
            'user_id' => $request->user()->id,
            'rating' => $data['rating'],
            'text' => $data['text'],
        ]);

        return redirect()->route('bookings.index')
            ->with('success', 'Спасибо, отзыв опубликован на главной странице.');
    }
}
