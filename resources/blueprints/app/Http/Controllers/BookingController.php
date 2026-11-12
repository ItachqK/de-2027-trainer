<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = $request->user()
            ->bookings()
            ->with('review')
            ->latest()
            ->paginate(8);

        return view('bookings.index', [
            'bookings' => $bookings,
        ]);
    }

    public function create()
    {
        return view('bookings.create', [
            'options' => array_map('trim', explode(',', '@@OPTIONS@@')),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'object_title' => ['required', 'string', 'min:2', 'max:120'],
            'event_date' => ['required', 'date', 'after_or_equal:today'],
            'payment' => ['required', 'in:onsite,sbp'],
            'comment' => ['nullable', 'string', 'max:500'],
        ], [
            'object_title.required' => 'Укажите, что вы бронируете.',
            'object_title.min' => 'Слишком короткое название.',
            'event_date.after_or_equal' => 'Дата не может быть в прошлом.',
            'payment.in' => 'Выберите способ оплаты из двух вариантов.',
            'comment.max' => 'Комментарий длиннее 500 символов.',
        ]);

        $booking = $request->user()->bookings()->create([
            'object_title' => $data['object_title'],
            'event_date' => $data['event_date'],
            'payment' => $data['payment'],
            'status' => 'new',
            'comment' => $data['comment'] ?? null,
        ]);

        return redirect()->route('bookings.index')
            ->with('success', 'Заявка №'.$booking->id.' принята. Статус — «'.Booking::STATUSES['new'].'».');
    }
}
