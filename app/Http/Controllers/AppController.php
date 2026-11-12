<?php

namespace App\Http\Controllers;

use App\Models\UserState;

class AppController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $state = null;

        if ($user) {
            $state = UserState::where('user_id', $user->id)->value('data');
        }

        return view('app', [
            'authUser' => $user?->only(['id', 'fio', 'login', 'role']),
            'serverState' => $state,
        ]);
    }
}
