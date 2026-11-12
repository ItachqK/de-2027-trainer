<?php

return [
    'app/Http/Controllers/Controller.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace App\Http\Controllers;

abstract class Controller
{
    //
}
PHP,
    ],
    'app/Providers/AppServiceProvider.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}
PHP,
    ],
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
            $table->string('fio');
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
    'database/migrations/2026_01_01_000001_create_rooms_table.php' => [
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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('capacity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
PHP,
    ],
    'database/migrations/2026_01_01_000002_create_applications_table.php' => [
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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->string('payment_type');
            $table->string('status')->default('new');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('review')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
PHP,
    ],
    'app/Models/User.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = ['fio', 'login', 'phone', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
PHP,
    ],
    'app/Models/Room.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'capacity'];

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
PHP,
    ],
    'app/Models/Application.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_id',
        'start_date',
        'payment_type',
        'status',
        'rating',
        'review',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'new' => 'Новая',
            'assigned' => 'Мероприятие назначено',
            'completed' => 'Завершено',
            default => $this->status,
        };
    }

    public function paymentLabel(): string
    {
        return match ($this->payment_type) {
            'onsite' => 'При очном посещении',
            'sbp' => 'Переводом по СБП',
            default => $this->payment_type,
        };
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
            RoomSeeder::class,
            ApplicationSeeder::class,
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
            ['login' => 'Conf2027'],
            [
                'fio' => 'Администратор портала',
                'phone' => '8(351)000-00-01',
                'email' => 'admin@conference.ru',
                'password' => Hash::make('Demo77'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['login' => 'maria2027'],
            [
                'fio' => 'Иванова Мария Сергеевна',
                'phone' => '8(351)000-00-02',
                'email' => 'student@conference.ru',
                'password' => Hash::make('Student2027!'),
                'role' => 'student',
            ]
        );
    }
}
PHP,
    ],
    'database/seeders/RoomSeeder.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = [
            ['name' => 'Аудитория', 'capacity' => 120],
            ['name' => 'Коворкинг', 'capacity' => 40],
            ['name' => 'Кинозал', 'capacity' => 80],
        ];

        foreach ($rooms as $room) {
            Room::updateOrCreate(['name' => $room['name']], $room);
        }
    }
}
PHP,
    ],
    'database/seeders/ApplicationSeeder.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $student = User::where('login', 'maria2027')->first();
        $rooms = Room::orderBy('id')->get();

        if (! $student || $rooms->count() < 3) {
            return;
        }

        Application::updateOrCreate(
            ['user_id' => $student->id, 'start_date' => '2026-11-12'],
            [
                'room_id' => $rooms[1]->id,
                'payment_type' => 'sbp',
                'status' => 'new',
            ]
        );

        Application::updateOrCreate(
            ['user_id' => $student->id, 'start_date' => '2026-10-01'],
            [
                'room_id' => $rooms[2]->id,
                'payment_type' => 'onsite',
                'status' => 'completed',
                'rating' => 5,
                'review' => 'Всё понравилось, зал подготовили вовремя.',
            ]
        );
    }
}
PHP,
    ],
    'app/Http/Middleware/EnsureUserIsAdmin.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isAdmin()) {
            abort(403);
        }

        return $next($request);
    }
}
PHP,
    ],
    'bootstrap/app.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
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
            'fio' => ['required', 'regex:/^[А-Яа-яЁё\s]+$/u', 'max:255'],
            'login' => ['required', 'regex:/^[A-Za-z0-9]{6,}$/', 'unique:users,login', 'max:50'],
            'phone' => ['required', 'regex:/^8\(\d{3}\)\d{3}-\d{2}-\d{2}$/'],
            'email' => ['required', 'email', 'unique:users,email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'fio.regex' => 'ФИО: только кириллица и пробелы.',
            'login.regex' => 'Логин: латиница и цифры, не менее 6 символов.',
            'phone.regex' => 'Телефон в формате 8(XXX)XXX-XX-XX.',
            'password.min' => 'Пароль — не менее 8 символов.',
            'password.confirmed' => 'Пароли не совпадают.',
        ]);

        $user = User::create([
            'fio' => $data['fio'],
            'login' => $data['login'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'student',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->to('/profile')->with('success', 'Регистрация прошла. Добро пожаловать.');
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

        return redirect()->intended('/profile');
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
    'app/Http/Controllers/ApplicationController.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Room;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function create()
    {
        return view('application', [
            'rooms' => Room::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'payment_type' => ['required', 'in:onsite,sbp'],
        ], [
            'room_id.exists' => 'Выберите помещение из списка.',
            'start_date.after_or_equal' => 'Дата не может быть в прошлом.',
            'payment_type.in' => 'Выберите способ оплаты.',
        ]);

        $request->user()->applications()->create([
            'room_id' => $data['room_id'],
            'start_date' => $data['start_date'],
            'payment_type' => $data['payment_type'],
            'status' => 'new',
        ]);

        return redirect()->route('profile')->with('success', 'Заявка отправлена.');
    }

    public function profile(Request $request)
    {
        return view('profile', [
            'applications' => $request->user()
                ->applications()
                ->with('room')
                ->latest()
                ->get(),
        ]);
    }

    public function review(Request $request, Application $application)
    {
        abort_unless($application->user_id === $request->user()->id, 403);
        abort_unless($application->status === 'completed', 403);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'review' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'rating.between' => 'Оценка — от 1 до 5.',
            'review.min' => 'Отзыв — не менее 10 символов.',
        ]);

        $application->update($data);

        return back()->with('success', 'Спасибо за отзыв.');
    }
}
PHP,
    ],
    'app/Http/Controllers/AdminController.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $q = trim((string) $request->query('q'));

        $applications = Application::with(['user', 'room'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($q !== '', function ($query) use ($q) {
                $query->whereHas('user', fn ($user) => $user->where('fio', 'like', "%{$q}%"));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.index', [
            'applications' => $applications,
            'status' => $status,
            'q' => $q,
        ]);
    }

    public function status(Request $request, Application $application)
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,assigned,completed'],
        ]);

        $application->update(['status' => $data['status']]);

        return back()->with('success', 'Статус обновлён.');
    }
}
PHP,
    ],
    'routes/web.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AuthController;
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
    Route::get('/application', [ApplicationController::class, 'create'])->name('application.create');
    Route::post('/application', [ApplicationController::class, 'store'])->name('application.store');
    Route::get('/profile', [ApplicationController::class, 'profile'])->name('profile');
    Route::post('/applications/{application}/review', [ApplicationController::class, 'review'])->name('applications.review');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::post('/applications/{application}/status', [AdminController::class, 'status'])->name('status');
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
    <title>@yield('title', 'Конференции.РФ')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<header class="top">
    <a class="brand" href="{{ route('home') }}">Конференции.РФ</a>
    <nav class="nav">
        @auth
            <a href="{{ url('/application') }}">Заявка</a>
            <a href="{{ url('/profile') }}">Кабинет</a>
            @if (auth()->user()->isAdmin())
                <a href="{{ url('/admin') }}">Админка</a>
            @endif
            <form method="POST" action="{{ url('/logout') }}">
                @csrf
                <button type="submit" class="link">Выйти</button>
            </form>
        @else
            <a href="{{ url('/login') }}">Вход</a>
            <a href="{{ url('/register') }}">Регистрация</a>
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

@section('title', 'Конференции — главная')

@section('content')
    <section class="hero">
        <span class="eyebrow">Конференции.РФ</span>
        <h1>Бронирование помещений для конференций</h1>
        <p>Аудитория, коворкинг и кинозал. Выберите помещение, дату начала и способ оплаты — заявка уйдёт администратору портала.</p>
        <div class="actions">
            <a class="btn" href="{{ url('/application') }}">Оставить заявку</a>
            <a class="btn ghost" href="{{ url('/register') }}">Зарегистрироваться</a>
        </div>
    </section>

    <section class="cards">
        <article class="card">
            <h2>Аудитория</h2>
            <p>120 мест. Большой зал для пленарных заседаний.</p>
        </article>
        <article class="card">
            <h2>Коворкинг</h2>
            <p>40 мест. Гибкая рассадка для рабочих встреч.</p>
        </article>
        <article class="card">
            <h2>Кинозал</h2>
            <p>80 мест. Показ презентаций и записей докладов.</p>
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
                <label for="fio">ФИО</label>
                <input id="fio" name="fio" value="{{ old('fio') }}" required>
                @error('fio') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="login">Логин</label>
                <input id="login" name="login" value="{{ old('login') }}" required>
                @error('login') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="phone">Телефон</label>
                <input id="phone" name="phone" value="{{ old('phone') }}" placeholder="8(351)000-00-02" required>
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

            <button class="btn" type="submit">Создать пользователя</button>
        </form>

        <p class="muted">Уже есть аккаунт? <a href="{{ url('/login') }}">Войти</a></p>
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
                @error('password') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <button class="btn" type="submit">Войти</button>
        </form>

        <p class="muted">Еще не зарегистрированы? <a href="{{ url('/register') }}">Регистрация</a></p>
    </section>
@endsection
BLADE,
    ],
    'resources/views/application.blade.php' => [
        'language' => 'blade',
        'code' => <<<'BLADE'
@extends('layouts.app')

@section('title', 'Новая заявка')

@section('content')
    <section class="panel narrow">
        <h1>Заявка на конференцию</h1>

        <form method="POST" action="{{ route('application.store') }}" novalidate>
            @csrf

            <div class="field">
                <label for="room_id">Помещение</label>
                <select id="room_id" name="room_id" required>
                    <option value="">Выберите помещение</option>
                    @foreach ($rooms as $room)
                        <option value="{{ $room->id }}" @selected(old('room_id') == $room->id)>
                            {{ $room->name }} · {{ $room->capacity }} мест
                        </option>
                    @endforeach
                </select>
                @error('room_id') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="start_date">Дата начала</label>
                <input id="start_date" type="date" name="start_date" value="{{ old('start_date') }}" required>
                @error('start_date') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="payment_type">Способ оплаты</label>
                <select id="payment_type" name="payment_type" required>
                    <option value="">Выберите способ</option>
                    <option value="onsite" @selected(old('payment_type') === 'onsite')>При очном посещении</option>
                    <option value="sbp" @selected(old('payment_type') === 'sbp')>Переводом по СБП</option>
                </select>
                @error('payment_type') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <button class="btn" type="submit">Отправить</button>
        </form>
    </section>
@endsection
BLADE,
    ],
    'resources/views/profile.blade.php' => [
        'language' => 'blade',
        'code' => <<<'BLADE'
@extends('layouts.app')

@section('title', 'Кабинет')

@section('content')
    <section class="panel">
        <div class="panel-head">
            <h1>Мои заявки</h1>
            <a class="btn" href="{{ route('application.create') }}">Новая заявка</a>
        </div>

        @if ($applications->isEmpty())
            <p class="muted">Пока нет заявок. Создайте первую.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Помещение</th>
                            <th>Дата</th>
                            <th>Оплата</th>
                            <th>Статус</th>
                            <th>Отзыв</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($applications as $application)
                            <tr>
                                <td>{{ $application->room->name }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($application->start_date)->format('d.m.Y') }}</td>
                                <td>{{ $application->paymentLabel() }}</td>
                                <td><span class="status status-{{ $application->status }}">{{ $application->statusLabel() }}</span></td>
                                <td>
                                    @if ($application->status === 'completed')
                                        @if ($application->review)
                                            <span class="muted">Оценка {{ $application->rating }}/5</span>
                                        @else
                                            <form method="POST" action="{{ route('applications.review', $application) }}" class="review-form">
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
                                        <span class="muted">После завершения</span>
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
    'resources/views/admin/index.blade.php' => [
        'language' => 'blade',
        'code' => <<<'BLADE'
@extends('layouts.app')

@section('title', 'Админка')

@section('content')
    <section class="panel">
        <div class="panel-head">
            <h1>Все заявки</h1>
            <form method="GET" action="{{ route('admin.index') }}" class="filter">
                <input name="q" value="{{ $q }}" placeholder="Поиск по ФИО">
                <select name="status">
                    <option value="">Все статусы</option>
                    @foreach (['new' => 'Новая', 'assigned' => 'Мероприятие назначено', 'completed' => 'Завершено'] as $key => $label)
                        <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="btn small" type="submit">Найти</button>
            </form>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Пользователь</th>
                        <th>Помещение</th>
                        <th>Дата</th>
                        <th>Оплата</th>
                        <th>Статус</th>
                        <th>Изменить</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($applications as $application)
                        <tr>
                            <td>{{ $application->user->fio }}</td>
                            <td>{{ $application->room->name }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($application->start_date)->format('d.m.Y') }}</td>
                            <td>{{ $application->paymentLabel() }}</td>
                            <td><span class="status status-{{ $application->status }}">{{ $application->statusLabel() }}</span></td>
                            <td>
                                <form method="POST" action="{{ route('admin.status', $application) }}" class="status-form">
                                    @csrf
                                    <select name="status">
                                        @foreach (['new' => 'Новая', 'assigned' => 'Мероприятие назначено', 'completed' => 'Завершено'] as $key => $label)
                                            <option value="{{ $key }}" @selected($application->status === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn small" type="submit">Сохранить</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="muted">Заявок нет.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $applications->links() }}</div>
    </section>
@endsection
BLADE,
    ],
    'public/css/app.css' => [
        'language' => 'css',
        'code' => <<<'CSS'
:root {
    --bg: #f4f1e9;
    --surface: #fffdf7;
    --ink: #1b1d1f;
    --muted: #6b6f6d;
    --line: #d4cfc2;
    --accent: #c2410c;
    --ok: #2f6b4f;
    --danger: #b42318;
}

* { box-sizing: border-box; }

body {
    margin: 0;
    background: var(--bg);
    color: var(--ink);
    font: 16px/1.55 "Segoe UI", Arial, sans-serif;
}

a { color: #1d4ed8; }

.top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 14px 28px;
    background: #17191b;
    color: #f4f1e9;
}

.brand {
    color: #fff;
    font-weight: 700;
    font-size: 20px;
    text-decoration: none;
}

.nav {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    font-size: 14px;
}

.nav a, .nav .link {
    color: #d8d4c8;
    text-decoration: none;
    background: none;
    border: 0;
    padding: 0;
    font: inherit;
    cursor: pointer;
}

.nav a:hover, .nav .link:hover { color: #fff; }

.wrap {
    max-width: 1100px;
    margin: 0 auto;
    padding: 32px 24px 64px;
}

.hero { padding: 40px 0 28px; border-bottom: 1px solid var(--line); }
.eyebrow { color: var(--accent); font-size: 12px; letter-spacing: .12em; text-transform: uppercase; }
h1 { font-size: clamp(32px, 5vw, 52px); line-height: 1.05; margin: 8px 0 12px; }
.hero p { max-width: 62ch; color: var(--muted); }

.actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 20px; }

.btn {
    display: inline-block;
    padding: 11px 18px;
    background: var(--accent);
    color: #fff;
    border: 1px solid var(--accent);
    text-decoration: none;
    font-size: 14px;
    cursor: pointer;
}

.btn:hover { background: #9a3412; }
.btn.ghost { background: transparent; color: var(--ink); border-color: var(--ink); }
.btn.small { padding: 7px 10px; font-size: 12px; }

.cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-top: 28px;
}

.card, .panel {
    background: var(--surface);
    border: 1px solid var(--line);
    padding: 20px;
}

.card h2 { margin: 0 0 6px; font-size: 20px; }
.card p { margin: 0; color: var(--muted); font-size: 14px; }

.panel { margin-top: 24px; }
.panel.narrow { max-width: 560px; margin: 24px auto; }
.panel-head { display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; }
.panel-head h1 { font-size: 30px; margin: 0; }

.field { display: grid; gap: 6px; margin-bottom: 14px; }
.field label { font-size: 13px; font-weight: 600; }
.field input, .field select, .field textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--line);
    background: #fff;
    font: inherit;
}
.field input:focus, .field select:focus, .field textarea:focus {
    outline: 2px solid var(--accent);
    outline-offset: 1px;
}

.error-text { color: var(--danger); font-size: 12px; }

.alert { padding: 12px 14px; margin: 18px 0; border: 1px solid var(--line); font-size: 14px; }
.alert.ok { border-color: var(--ok); background: #e7f1eb; color: var(--ok); }
.alert.error { border-color: var(--danger); background: #f9e5e2; color: var(--danger); }

.table-wrap { overflow-x: auto; margin-top: 16px; }
table { width: 100%; border-collapse: collapse; font-size: 13px; }
th, td { text-align: left; padding: 10px 8px; border-bottom: 1px solid var(--line); vertical-align: top; }
th { color: var(--muted); font-size: 11px; text-transform: uppercase; letter-spacing: .06em; }

.status {
    display: inline-block;
    padding: 3px 8px;
    border: 1px solid var(--line);
    font-size: 11px;
    text-transform: uppercase;
}

.review-form, .status-form, .filter {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    align-items: center;
}

.review-form input { min-width: 160px; }

.muted { color: var(--muted); font-size: 13px; }
.pagination { margin-top: 16px; }

@media (max-width: 760px) {
    .cards { grid-template-columns: 1fr; }
    .top { flex-direction: column; align-items: flex-start; }
    .wrap { padding: 20px 14px 48px; }
}

/* ===== Дизайн-концепция КИМ ===== */
/* Цвета: #007bff, #0d47a1, #6c757d, #f8f9fa, #ffffff. Шрифт: PT Sans. */

@font-face {
    font-family: 'PT Sans';
    src: url('/fonts/PTSans-Regular.ttf') format('truetype');
    font-weight: 400;
    font-style: normal;
    font-display: swap;
}

@font-face {
    font-family: 'PT Sans';
    src: url('/fonts/PTSans-Bold.ttf') format('truetype');
    font-weight: 700;
    font-style: normal;
    font-display: swap;
}

@font-face {
    font-family: 'PT Sans';
    src: url('/fonts/PTSans-Italic.ttf') format('truetype');
    font-weight: 400;
    font-style: italic;
    font-display: swap;
}

:root {
    --bg: #f8f9fa;
    --surface: #ffffff;
    --ink: #212529;
    --muted: #6c757d;
    --line: #dee2e6;
    --accent: #007bff;
    --accent-dark: #0d47a1;
    --ok: #0f5132;
    --danger: #dc3545;
}

body {
    background: var(--bg);
    color: var(--ink);
    font-family: 'PT Sans', Arial, sans-serif;
    font-size: 16px;
    line-height: 1.6;
}

h1 { font-size: 36px; font-weight: 700; line-height: 1.2; }
h2 { font-size: 24px; font-weight: 700; }
h3 { font-size: 18px; font-weight: 700; }

.top {
    background: var(--surface);
    color: var(--accent-dark);
    box-shadow: 0 1px 4px rgba(0, 0, 0, .08);
    position: sticky;
    top: 0;
    z-index: 20;
}

.brand { color: var(--accent-dark); font-size: 20px; }

.nav a, .nav .link { color: var(--accent-dark); }
.nav a:hover, .nav .link:hover { color: var(--accent); }

.hero { border-bottom: 1px solid var(--line); background: var(--surface); }
.eyebrow { color: var(--accent); font-size: 12px; font-style: italic; letter-spacing: .04em; text-transform: none; }
.hero p { color: var(--muted); }

.btn {
    background: var(--accent);
    border-color: var(--accent);
    color: #fff;
    border-radius: 6px;
}

.btn:hover { background: var(--accent-dark); border-color: var(--accent-dark); }
.btn.ghost { background: transparent; color: var(--accent); border-color: var(--accent); }
.btn.ghost:hover { background: var(--accent); color: #fff; }

.card, .panel {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, .06);
}

.card h2 { color: var(--accent-dark); }

.field input, .field select, .field textarea {
    border: 1px solid #ced4da;
    border-radius: 6px;
}

.field input:focus, .field select:focus, .field textarea:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(0, 123, 255, .15);
}

.alert.ok { border-color: #badbcc; background: #d1e7dd; color: var(--ok); }
.alert.error { border-color: #f5c2c7; background: #f8d7da; color: #842029; }

table { font-size: 15px; }
th { background: var(--bg); color: var(--muted); font-size: 13px; text-transform: uppercase; }

.status {
    border: 0;
    border-radius: 20px;
    padding: 4px 10px;
    font-size: 12px;
    font-weight: 700;
    text-transform: none;
}

.status-new { background: #cfe2ff; color: #084298; }
.status-assigned { background: #d1e7dd; color: #0f5132; }
.status-completed { background: #e2e3e5; color: #41464b; }

.muted { color: var(--muted); }

@media (max-width: 760px) {
    h1 { font-size: 28px; }
    .top { position: static; }
}
CSS,
    ],
    'tests/Feature/ApplicationTest.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_cannot_open_profile(): void
    {
        $this->get('/profile')->assertRedirect('/login');
    }

    public function test_registration_creates_student(): void
    {
        $response = $this->post('/register', [
            'fio' => 'Петров Пётр Петрович',
            'login' => 'НовыйЛогин',
            'email' => 'new@example.ru',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect('/profile');
        $this->assertDatabaseHas('users', [
            'login' => 'НовыйЛогин',
            'role' => 'student',
        ]);
    }

    public function test_student_can_login(): void
    {
        $this->post('/login', [
            'login' => 'student',
            'password' => 'Student2027!',
        ])->assertRedirect('/profile');

        $this->assertAuthenticated();
    }

    public function test_student_can_create_application(): void
    {
        $student = User::where('login', 'student')->firstOrFail();
        $room = Room::firstOrFail();

        $this->actingAs($student)->post('/application', [
            'room_id' => $room->id,
            'start_date' => now()->addDay()->toDateString(),
            'payment_type' => 'card',
        ])->assertRedirect('/profile');

        $this->assertDatabaseHas('applications', [
            'user_id' => $student->id,
            'room_id' => $room->id,
            'payment_type' => 'card',
        ]);
    }

    public function test_user_cannot_review_someone_elses_application(): void
    {
        $owner = User::where('login', 'student')->firstOrFail();
        $other = User::create([
            'fio' => 'Другой Пользователь',
            'login' => 'ДругойЛогин',
            'email' => 'other@example.ru',
            'password' => Hash::make('secret123'),
            'role' => 'student',
        ]);

        $application = Application::where('user_id', $owner->id)->firstOrFail();

        $this->actingAs($other)->post('/applications/'.$application->id.'/review', [
            'rating' => 5,
            'review' => 'Чужой отзыв, не должен пройти.',
        ])->assertForbidden();
    }

    public function test_admin_can_change_status(): void
    {
        $admin = User::where('login', 'admin')->firstOrFail();
        $application = Application::firstOrFail();

        $this->actingAs($admin)->post('/admin/applications/'.$application->id.'/status', [
            'status' => 'completed',
        ])->assertRedirect();

        $this->assertSame('completed', $application->fresh()->status);
    }
}
PHP,
    ],
    'README.md' => [
        'language' => 'markdown',
        'code' => <<<'MD'
# Конференции — эталонный проект

Мини-проект для демоэкзамена 09.02.07: регистрация, вход, заявки, кабинет, отзыв и админка со статусами.

## Запуск

```bash
composer create-project laravel/laravel conference
cd conference
```

Скопируйте файлы из архива в проект, сохранив структуру. Затем:

```bash
copy .env.example .env
php artisan key:generate
type nul > database\database.sqlite
php artisan migrate --seed
php artisan serve
```

Откройте `http://127.0.0.1:8000`.

## Аккаунты

- Администратор: `admin` / `Admin2027!`
- Студент: `student` / `Student2027!`

## Проверка

```bash
php artisan test
```

Сценарий: регистрация → вход → заявка → кабинет → админка → статус → отзыв.
MD,
    ],

    'database/factories/UserFactory.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'fio' => fake()->name(),
            'login' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => 'student',
        ];
    }

    public function create($attributes = [], ?Model $parent = null)
    {
        unset($attributes['name']);

        return parent::create($attributes, $parent);
    }
}
PHP
    ],
];
