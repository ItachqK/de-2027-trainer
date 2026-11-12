<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'fio' => ['required', 'string', 'max:120', 'regex:/^[А-Яа-яЁё\s-]+$/u'],
            'login' => ['required', 'string', 'min:6', 'max:60', 'regex:/^[A-Za-z0-9]+$/', 'unique:users,login'],
            'phone' => ['required', 'string', 'regex:/^8\(\d{3}\)\d{3}-\d{2}-\d{2}$/'],
            'email' => ['required', 'string', 'email', 'max:120', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'fio.required' => 'Укажите фамилию, имя и отчество.',
            'fio.regex' => 'ФИО — только кириллица, пробелы и дефис.',
            'login.required' => 'Придумайте логин.',
            'login.min' => 'Логин короче 6 символов.',
            'login.regex' => 'Логин — только латиница и цифры, без пробелов.',
            'login.unique' => 'Такой логин уже занят. Добавьте цифры.',
            'phone.regex' => 'Телефон в формате 8(912)345-67-89.',
            'email.unique' => 'На эту почту уже зарегистрирован аккаунт.',
            'password.min' => 'Пароль короче 8 символов.',
            'password.confirmed' => 'Пароли не совпадают.',
            'password.letters' => 'В пароле нужна хотя бы одна буква.',
            'password.numbers' => 'В пароле нужна хотя бы одна цифра.',
        ]);

        $user = User::create([
            'fio' => $data['fio'],
            'login' => $data['login'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'user',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('bookings.index')
            ->with('success', 'Аккаунт создан. Оформите первую заявку.');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Введите логин.',
            'password.required' => 'Введите пароль.',
        ]);

        $key = Str::lower($data['login']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'login' => 'Слишком много попыток. Подождите минуту и попробуйте снова.',
            ]);
        }

        if (! Auth::attempt(['login' => $data['login'], 'password' => $data['password']], $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'login' => 'Неверный логин или пароль.',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->intended(route('bookings.index'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Вы вышли из аккаунта.');
    }
}
