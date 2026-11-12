<?php

$base = require __DIR__.'/../build-files.php';

$files = [
    'database/migrations/0001_01_01_000000_create_users_table.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('login')->unique();
            $table->string('phone');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('student');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
    }
};
PHP,
    ],
    'database/migrations/2026_01_01_000001_create_bookings_table.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('visit_date');
            $table->string('visit_time', 5);
            $table->unsignedTinyInteger('guests');
            $table->string('phone');
            $table->string('status')->default('new');
            $table->text('review')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
PHP,
    ],
    'app/Models/User.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'first_name',
        'last_name',
        'login',
        'phone',
        'email',
        'password',
        'role',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
PHP,
    ],
    'app/Models/Booking.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'visit_date',
        'visit_time',
        'guests',
        'phone',
        'status',
        'review',
        'rating',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
PHP,
    ],
    'database/seeders/DatabaseSeeder.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            BookingSeeder::class,
        ]);
    }
}
PHP,
    ],
    'database/seeders/UserSeeder.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['login' => 'admin'],
            [
                'first_name' => 'Администратор',
                'last_name' => 'Ресторана',
                'phone' => '+7(900)-000-00-01',
                'email' => 'admin@restaurant.ru',
                'password' => Hash::make('restaurant'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['login' => 'student'],
            [
                'first_name' => 'Мария',
                'last_name' => 'Иванова',
                'phone' => '+7(900)-000-00-02',
                'email' => 'student@restaurant.ru',
                'password' => Hash::make('Student2027!'),
                'role' => 'student',
            ]
        );
    }
}
PHP,
    ],
    'database/seeders/BookingSeeder.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $student = User::where('login', 'student')->first();

        if (! $student) {
            return;
        }

        Booking::updateOrCreate(
            ['user_id' => $student->id, 'visit_date' => '2026-11-20'],
            [
                'visit_time' => '19:30',
                'guests' => 4,
                'phone' => '+7(900)-000-00-02',
                'status' => 'new',
            ]
        );

        Booking::updateOrCreate(
            ['user_id' => $student->id, 'visit_date' => '2026-10-01'],
            [
                'visit_time' => '18:00',
                'guests' => 2,
                'phone' => '+7(900)-000-00-02',
                'status' => 'visited',
            ]
        );
    }
}
PHP,
    ],
    'app/Http/Controllers/AuthController.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
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
            'first_name' => ['required', 'regex:/^[А-Яа-яЁё-]+$/u', 'max:100'],
            'last_name' => ['required', 'regex:/^[А-Яа-яЁё-]+$/u', 'max:100'],
            'login' => ['required', 'regex:/^[А-Яа-яЁё0-9]{6,}$/u', 'unique:users,login', 'max:50'],
            'phone' => ['required', 'regex:/^\+7\(\d{3}\)-\d{3}-\d{2}-\d{2}$/'],
            'email' => ['required', 'email', 'unique:users,email', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'login.regex' => 'Логин: кириллица или цифры, не менее 6 символов.',
            'phone.regex' => 'Телефон в формате +7(XXX)-XXX-XX-XX.',
            'password.min' => 'Пароль — не менее 6 символов.',
            'password.confirmed' => 'Пароли не совпадают.',
        ]);

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'login' => $data['login'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'student',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('bookings.index')->with('success', 'Регистрация прошла.');
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
        ]);

        $credentials['login'] = trim($credentials['login']);

        if (! Auth::attempt($credentials)) {
            return back()
                ->withErrors(['login' => 'Неверный логин или пароль.'])
                ->withInput();
        }

        $request->session()->regenerate();

        return redirect()->intended(route('bookings.index'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
PHP,
    ],
    'app/Http/Controllers/BookingController.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        return view('bookings.index', [
            'bookings' => $request->user()
                ->bookings()
                ->latest('visit_date')
                ->get(),
        ]);
    }

    public function create()
    {
        return view('booking.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'visit_date' => ['required', 'date', 'after_or_equal:today'],
            'visit_time' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'guests' => ['required', 'integer', 'between:1,10'],
            'phone' => ['required', 'regex:/^\+7\(\d{3}\)-\d{3}-\d{2}-\d{2}$/'],
        ], [
            'visit_date.after_or_equal' => 'Дата не может быть в прошлом.',
            'visit_time.regex' => 'Время в формате ЧЧ:ММ.',
            'guests.between' => 'Гостей — от 1 до 10.',
            'phone.regex' => 'Телефон в формате +7(XXX)-XXX-XX-XX.',
        ]);

        $request->user()->bookings()->create($data + ['status' => 'new']);

        return redirect()->route('bookings.index')->with('success', 'Столик забронирован.');
    }

    public function review(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id, 403);
        abort_unless($booking->status === 'visited', 403);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'review' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'rating.between' => 'Оценка — от 1 до 5.',
            'review.min' => 'Отзыв — не менее 10 символов.',
        ]);

        $booking->update($data);

        return back()->with('success', 'Спасибо за отзыв.');
    }
}
PHP,
    ],
    'app/Http/Controllers/AdminBookingController.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $bookings = Booking::with('user')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest('visit_date')
            ->paginate(10)
            ->withQueryString();

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'status' => $status,
        ]);
    }

    public function status(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,visited,cancelled'],
        ]);

        $booking->update(['status' => $data['status']]);

        return back()->with('success', 'Статус брони обновлён.');
    }
}
PHP,
    ],
    'routes/web.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

use App\Http\Controllers\AdminBookingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home'))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/booking', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
    Route::post('/bookings/{booking}/review', [BookingController::class, 'review'])->name('bookings.review');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminBookingController::class, 'index'])->name('index');
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings/{booking}/status', [AdminBookingController::class, 'status'])->name('bookings.status');
});
PHP,
    ],
    'resources/views/layouts/app.blade.php' => [
        'language' => 'blade',
        'code' => <<<'BLADE'
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Я буду кушац')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<header class="top">
    <a class="brand" href="{{ route('home') }}">Я буду кушац</a>
    <nav class="nav">
        @auth
            <a href="{{ route('bookings.index') }}">Мои брони</a>
            <a href="{{ route('booking.create') }}">Забронировать</a>
            @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.index') }}">Админка</a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="link">Выйти</button>
            </form>
        @else
            <a href="{{ route('login') }}">Вход</a>
            <a href="{{ route('register') }}">Регистрация</a>
        @endauth
    </nav>
</header>

<main class="wrap">
    @if (session('success'))
        <div class="alert ok">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert error">{{ $errors->first() }}</div>
    @endif

    @yield('content')
</main>
</body>
</html>
BLADE,
    ],
    'resources/views/home.blade.php' => [
        'language' => 'blade',
        'code' => <<<'BLADE'
@extends('layouts.app')

@section('title', 'Я буду кушац — бронирование')

@section('content')
    <section class="hero">
        <span class="eyebrow">Демоэкзамен 09.02.07 · КИМ 2025</span>
        <h1>Забронировать столик</h1>
        <p>Выберите дату, время и количество гостей. Администратор подтвердит визит или отменит бронь.</p>
        <div class="actions">
            <a class="btn" href="{{ route('booking.create') }}">Забронировать</a>
            <a class="btn ghost" href="{{ route('register') }}">Зарегистрироваться</a>
        </div>
    </section>

    <section class="cards">
        <article class="card">
            <h2>Дата и время</h2>
            <p>Дата из календаря, время в формате ЧЧ:ММ.</p>
        </article>
        <article class="card">
            <h2>Гости</h2>
            <p>От 1 до 10 человек, контактный телефон обязателен.</p>
        </article>
        <article class="card">
            <h2>Отзыв</h2>
            <p>Оставить отзыв можно только после состоявшегося визита.</p>
        </article>
    </section>
@endsection
BLADE,
    ],
    'resources/views/auth/register.blade.php' => [
        'language' => 'blade',
        'code' => <<<'BLADE'
@extends('layouts.app')

@section('title', 'Регистрация')

@section('content')
    <section class="panel narrow">
        <h1>Регистрация</h1>

        <form method="POST" action="{{ route('register.store') }}" novalidate>
            @csrf

            <div class="field">
                <label for="first_name">Имя</label>
                <input id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                @error('first_name') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="last_name">Фамилия</label>
                <input id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                @error('last_name') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="login">Логин</label>
                <input id="login" name="login" value="{{ old('login') }}" required>
                @error('login') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="phone">Телефон</label>
                <input id="phone" name="phone" value="{{ old('phone') }}" placeholder="+7(900)-000-00-00" required>
                @error('phone') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required>
                @error('email') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="password">Пароль</label>
                <input id="password" type="password" name="password" required>
                @error('password') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="password_confirmation">Повтор пароля</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required>
            </div>

            <button class="btn" type="submit">Зарегистрироваться</button>
        </form>

        <p class="muted">Уже есть аккаунт? <a href="{{ route('login') }}">Войти</a></p>
    </section>
@endsection
BLADE,
    ],
    'resources/views/auth/login.blade.php' => [
        'language' => 'blade',
        'code' => <<<'BLADE'
@extends('layouts.app')

@section('title', 'Вход')

@section('content')
    <section class="panel narrow">
        <h1>Вход</h1>

        <form method="POST" action="{{ route('login.store') }}" novalidate>
            @csrf

            <div class="field">
                <label for="login">Логин</label>
                <input id="login" name="login" value="{{ old('login') }}" required autofocus>
                @error('login') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="password">Пароль</label>
                <input id="password" type="password" name="password" required>
            </div>

            <button class="btn" type="submit">Войти</button>
        </form>

        <p class="muted">Нет аккаунта? <a href="{{ route('register') }}">Регистрация</a></p>
    </section>
@endsection
BLADE,
    ],
    'resources/views/bookings/index.blade.php' => [
        'language' => 'blade',
        'code' => <<<'BLADE'
@extends('layouts.app')

@section('title', 'Мои брони')

@section('content')
    <section class="panel">
        <div class="panel-head">
            <h1>Мои брони</h1>
            <a class="btn" href="{{ route('booking.create') }}">Забронировать</a>
        </div>

        @if ($bookings->isEmpty())
            <p class="muted">Броней пока нет.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Дата</th>
                            <th>Время</th>
                            <th>Гостей</th>
                            <th>Статус</th>
                            <th>Отзыв</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookings as $booking)
                            <tr>
                                <td>{{ $booking->visit_date }}</td>
                                <td>{{ $booking->visit_time }}</td>
                                <td>{{ $booking->guests }}</td>
                                <td><span class="status">{{ $booking->status }}</span></td>
                                <td>
                                    @if ($booking->status === 'visited')
                                        @if ($booking->review)
                                            <span class="muted">Оценка {{ $booking->rating }}/5</span>
                                        @else
                                            <form method="POST" action="{{ route('bookings.review', $booking) }}" class="review-form">
                                                @csrf
                                                <select name="rating" required>
                                                    <option value="">Оценка</option>
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                    @endfor
                                                </select>
                                                <input name="review" placeholder="Отзыв от 10 символов" required>
                                                <button class="btn small" type="submit">Оставить</button>
                                            </form>
                                        @endif
                                    @else
                                        <span class="muted">После визита</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
BLADE,
    ],
    'resources/views/booking/create.blade.php' => [
        'language' => 'blade',
        'code' => <<<'BLADE'
@extends('layouts.app')

@section('title', 'Бронирование')

@section('content')
    <section class="panel narrow">
        <h1>Забронировать столик</h1>

        <form method="POST" action="{{ route('booking.store') }}" novalidate>
            @csrf

            <div class="field">
                <label for="visit_date">Дата</label>
                <input id="visit_date" type="date" name="visit_date" value="{{ old('visit_date') }}" required>
                @error('visit_date') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="visit_time">Время</label>
                <input id="visit_time" type="time" name="visit_time" value="{{ old('visit_time') }}" required>
                @error('visit_time') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="guests">Гостей</label>
                <input id="guests" type="number" name="guests" min="1" max="10" value="{{ old('guests') }}" required>
                @error('guests') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="phone">Телефон</label>
                <input id="phone" name="phone" value="{{ old('phone') }}" placeholder="+7(900)-000-00-00" required>
                @error('phone') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <button class="btn" type="submit">Забронировать</button>
        </form>
    </section>
@endsection
BLADE,
    ],
    'resources/views/admin/bookings/index.blade.php' => [
        'language' => 'blade',
        'code' => <<<'BLADE'
@extends('layouts.app')

@section('title', 'Админка — брони')

@section('content')
    <section class="panel">
        <div class="panel-head">
            <h1>Все брони</h1>
            <form method="GET" action="{{ route('admin.bookings.index') }}" class="filter">
                <select name="status">
                    <option value="">Все статусы</option>
                    @foreach (['new' => 'Новое', 'visited' => 'Посещение состоялось', 'cancelled' => 'Отменено'] as $key => $label)
                        <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="btn small" type="submit">Фильтр</button>
            </form>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Гость</th>
                        <th>Дата</th>
                        <th>Время</th>
                        <th>Гостей</th>
                        <th>Телефон</th>
                        <th>Статус</th>
                        <th>Изменить</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td>{{ $booking->user->first_name }} {{ $booking->user->last_name }}</td>
                            <td>{{ $booking->visit_date }}</td>
                            <td>{{ $booking->visit_time }}</td>
                            <td>{{ $booking->guests }}</td>
                            <td>{{ $booking->phone }}</td>
                            <td><span class="status">{{ $booking->status }}</span></td>
                            <td>
                                <form method="POST" action="{{ route('admin.bookings.status', $booking) }}" class="status-form">
                                    @csrf
                                    <select name="status">
                                        @foreach (['new' => 'Новое', 'visited' => 'Посещение состоялось', 'cancelled' => 'Отменено'] as $key => $label)
                                            <option value="{{ $key }}" @selected($booking->status === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn small" type="submit">Сохранить</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="muted">Броней нет.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $bookings->links() }}</div>
    </section>
@endsection
BLADE,
    ],
    'tests/Feature/BookingTest.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_cannot_open_bookings(): void
    {
        $this->get('/bookings')->assertRedirect('/login');
    }

    public function test_registration_creates_student(): void
    {
        $this->post('/register', [
            'first_name' => 'Пётр',
            'last_name' => 'Петров',
            'login' => 'НовыйЛогин',
            'phone' => '+7(900)-111-22-33',
            'email' => 'new@restaurant.ru',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertRedirect('/bookings');

        $this->assertDatabaseHas('users', [
            'login' => 'НовыйЛогин',
            'role' => 'student',
        ]);
    }

    public function test_student_can_create_booking(): void
    {
        $student = User::where('login', 'student')->firstOrFail();

        $this->actingAs($student)->post('/booking', [
            'visit_date' => now()->addDay()->toDateString(),
            'visit_time' => '19:30',
            'guests' => 4,
            'phone' => '+7(900)-111-22-33',
        ])->assertRedirect('/bookings');

        $this->assertDatabaseHas('bookings', [
            'user_id' => $student->id,
            'visit_time' => '19:30',
            'guests' => 4,
            'status' => 'new',
        ]);
    }

    public function test_user_cannot_review_someone_elses_booking(): void
    {
        $owner = User::where('login', 'student')->firstOrFail();
        $other = User::create([
            'first_name' => 'Другой',
            'last_name' => 'Пользователь',
            'login' => 'ДругойЛогин',
            'phone' => '+7(900)-222-33-44',
            'email' => 'other@restaurant.ru',
            'password' => Hash::make('secret123'),
            'role' => 'student',
        ]);

        $booking = Booking::where('user_id', $owner->id)->firstOrFail();

        $this->actingAs($other)->post('/bookings/'.$booking->id.'/review', [
            'rating' => 5,
            'review' => 'Чужой отзыв не должен пройти.',
        ])->assertForbidden();
    }

    public function test_review_is_allowed_after_visit(): void
    {
        $student = User::where('login', 'student')->firstOrFail();
        $booking = Booking::where('user_id', $student->id)->where('status', 'visited')->firstOrFail();

        $this->actingAs($student)->post('/bookings/'.$booking->id.'/review', [
            'rating' => 5,
            'review' => 'Всё понравилось, столик был готов.',
        ])->assertRedirect();

        $this->assertSame(5, $booking->fresh()->rating);
    }

    public function test_admin_can_change_status(): void
    {
        $admin = User::where('login', 'admin')->firstOrFail();
        $booking = Booking::firstOrFail();

        $this->actingAs($admin)->post('/admin/bookings/'.$booking->id.'/status', [
            'status' => 'cancelled',
        ])->assertRedirect();

        $this->assertSame('cancelled', $booking->fresh()->status);
    }
}
PHP,
    ],
    'README.md' => [
        'language' => 'markdown',
        'code' => <<<'MD'
# Я буду кушац — эталонный проект

Бронирование столиков для демоэкзамена 09.02.07 (КИМ 2025).

## Запуск

```bash
composer create-project laravel/laravel restaurant
cd restaurant
```

Скопируйте файлы из архива, затем:

```bash
copy .env.example .env
php artisan key:generate
type nul > database\database.sqlite
php artisan migrate --seed
php artisan serve
```

## Аккаунты

- Администратор: `admin` / `restaurant`
- Студент: `student` / `Student2027!`

## Сценарий

Регистрация → вход → бронь → админка → статус «Посещение состоялось» → отзыв.
MD,
    ],
];

$files['app/Http/Controllers/Controller.php'] = $base['app/Http/Controllers/Controller.php'];
$files['app/Providers/AppServiceProvider.php'] = $base['app/Providers/AppServiceProvider.php'];
$files['app/Http/Middleware/EnsureUserIsAdmin.php'] = $base['app/Http/Middleware/EnsureUserIsAdmin.php'];
$files['bootstrap/app.php'] = $base['bootstrap/app.php'];
$files['public/css/app.css'] = $base['public/css/app.css'];

return $files;
