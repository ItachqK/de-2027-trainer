<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'fio' => ['required', 'string', 'regex:/^[А-ЯЁа-яё\s-]+$/u', 'max:255'],
            'login' => ['required', 'regex:/^[a-zA-Z0-9]{6,}$/', 'unique:users,login', 'max:50'],
            'email' => ['required', 'email', 'unique:users,email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ], [
            'fio.required' => 'ФИО обязательно для заполнения.',
            'fio.regex' => 'ФИО должно содержать только кириллицу, пробелы и дефис.',
            'login.required' => 'Логин обязателен.',
            'login.regex' => 'Логин: латинские буквы и цифры, не менее 6 символов.',
            'login.unique' => 'Пользователь с таким логином уже существует.',
            'email.required' => 'Email обязателен.',
            'email.email' => 'Введите корректный email.',
            'email.unique' => 'Пользователь с таким email уже существует.',
            'password.required' => 'Пароль обязателен.',
            'password.min' => 'Пароль должен быть не менее 8 символов.',
        ]);

        $user = User::create([
            'fio' => $data['fio'],
            'login' => $data['login'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'student',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('app')->with('success', 'Аккаунт создан. Прогресс будет сохраняться на сервере.');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Введите логин.',
            'password.required' => 'Введите пароль.',
        ]);

        $credentials['login'] = trim($credentials['login']);

        if (! Auth::attempt($credentials)) {
            return back()
                ->withErrors(['login' => 'Неверный логин или пароль.'])
                ->withInput();
        }

        $request->session()->regenerate();
        $request->user()->forceFill(['last_seen_at' => now()])->save();

        return redirect()->intended(route('app'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('logged_out', true);
    }
}
