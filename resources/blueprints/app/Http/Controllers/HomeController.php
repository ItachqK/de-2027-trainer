<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;

class HomeController extends Controller
{
    public function index()
    {
        return view('home', [
            'options' => array_map('trim', explode(',', '@@OPTIONS@@')),
            'reviews' => Review::query()
                ->with(['user:id,fio', 'booking:id,object_title'])
                ->latest()
                ->limit(3)
                ->get(),
        ]);
    }
}
