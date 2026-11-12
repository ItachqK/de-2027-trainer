<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('search'));

        $bookings = Booking::query()
            ->with(['user:id,fio,login,phone'])
            ->when(array_key_exists($status, Booking::STATUSES), fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('user', fn ($q) => $q->where('login', 'like', "%{$search}%"))
                        ->orWhere('object_title', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'status' => $status,
            'search' => $search,
            'counts' => [
                'all' => Booking::count(),
                'new' => Booking::where('status', 'new')->count(),
                'assigned' => Booking::where('status', 'assigned')->count(),
                'done' => Booking::where('status', 'done')->count(),
            ],
        ]);
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,assigned,done'],
        ], [
            'status.in' => 'Неизвестный статус заявки.',
        ]);

        if (! $booking->canMoveTo($data['status'])) {
            throw ValidationException::withMessages([
                'status' => 'Из статуса «'.$booking->statusLabel().'» нельзя перейти в «'.Booking::STATUSES[$data['status']].'».',
            ]);
        }

        $booking->update(['status' => $data['status']]);

        return back()->with('success', 'Заявка №'.$booking->id.' — «'.$booking->statusLabel().'».');
    }
}
