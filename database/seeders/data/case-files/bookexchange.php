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
    'database/migrations/2026_01_01_000001_create_cards_table.php' => [
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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('author');
            $table->string('title');
            $table->string('type');
            $table->string('publisher')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('binding')->nullable();
            $table->string('condition')->nullable();
            $table->string('status')->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
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
    protected $fillable = ['fio', 'login', 'phone', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
PHP,
    ],
    'app/Models/Card.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Card extends Model
{
    protected $fillable = [
        'user_id',
        'author',
        'title',
        'type',
        'publisher',
        'year',
        'binding',
        'condition',
        'status',
        'rejection_reason',
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
            CardSeeder::class,
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
                'fio' => 'Администратор портала',
                'phone' => '+7(900)-000-00-01',
                'email' => 'admin@book.ru',
                'password' => Hash::make('bookworm'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['login' => 'student'],
            [
                'fio' => 'Иванова Мария Сергеевна',
                'phone' => '+7(900)-000-00-02',
                'email' => 'student@book.ru',
                'password' => Hash::make('Student2027!'),
                'role' => 'student',
            ]
        );
    }
}
PHP,
    ],
    'database/seeders/CardSeeder.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\User;
use Illuminate\Database\Seeder;

class CardSeeder extends Seeder
{
    public function run(): void
    {
        $student = User::where('login', 'student')->first();

        if (! $student) {
            return;
        }

        Card::updateOrCreate(
            ['user_id' => $student->id, 'title' => 'Мастер и Маргарита'],
            [
                'author' => 'Михаил Булгаков',
                'type' => 'share',
                'publisher' => 'АСТ',
                'year' => 2020,
                'binding' => 'hard',
                'condition' => 'normal',
                'status' => 'published',
            ]
        );

        Card::updateOrCreate(
            ['user_id' => $student->id, 'title' => 'Чистый код'],
            [
                'author' => 'Роберт Мартин',
                'type' => 'want',
                'publisher' => 'Питер',
                'year' => 2019,
                'binding' => 'soft',
                'condition' => 'ideal',
                'status' => 'pending',
            ]
        );

        Card::updateOrCreate(
            ['user_id' => $student->id, 'title' => 'Плохая карточка'],
            [
                'author' => 'Неизвестный автор',
                'type' => 'share',
                'status' => 'rejected',
                'rejection_reason' => 'Не указано издательство и состояние книги.',
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
            'fio' => ['required', 'regex:/^[А-Яа-яЁё\s-]+$/u', 'max:255'],
            'login' => ['required', 'regex:/^[А-Яа-яЁё0-9]{6,}$/u', 'unique:users,login', 'max:50'],
            'phone' => ['required', 'regex:/^\+7\(\d{3}\)-\d{3}-\d{2}-\d{2}$/'],
            'email' => ['required', 'email', 'unique:users,email', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'fio.regex' => 'ФИО: только кириллица, пробелы и дефис.',
            'login.regex' => 'Логин: кириллица или цифры, не менее 6 символов.',
            'phone.regex' => 'Телефон в формате +7(XXX)-XXX-XX-XX.',
            'password.min' => 'Пароль — не менее 6 символов.',
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

        return redirect()->route('cards.index')->with('success', 'Регистрация прошла.');
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

        return redirect()->intended(route('cards.index'));
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
    'app/Http/Controllers/CardController.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function index(Request $request)
    {
        $cards = $request->user()->cards()
            ->whereIn('status', ['pending', 'published'])
            ->latest()
            ->get();

        $archive = $request->user()->cards()
            ->whereIn('status', ['rejected', 'deleted'])
            ->latest()
            ->get();

        return view('cards.index', [
            'cards' => $cards,
            'archive' => $archive,
        ]);
    }

    public function create()
    {
        return view('cards.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'author' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:share,want'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'between:1800,2100'],
            'binding' => ['nullable', 'in:hard,soft'],
            'condition' => ['nullable', 'in:ideal,normal,attention,leg'],
        ], [
            'author.required' => 'Укажите автора книги.',
            'title.required' => 'Укажите название книги.',
            'type.in' => 'Выберите тип карточки.',
        ]);

        $request->user()->cards()->create($data + ['status' => 'pending']);

        return redirect()->route('cards.index')->with('success', 'Карточка отправлена на проверку.');
    }

    public function destroy(Request $request, Card $card)
    {
        abort_unless($card->user_id === $request->user()->id, 403);

        $card->update(['status' => 'deleted']);

        return back()->with('success', 'Карточка перемещена в архив.');
    }
}
PHP,
    ],
    'app/Http/Controllers/AdminCardController.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Request;

class AdminCardController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $cards = Card::with('user')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.cards.index', [
            'cards' => $cards,
            'status' => $status,
        ]);
    }

    public function publish(Card $card)
    {
        $card->update([
            'status' => 'published',
            'rejection_reason' => null,
        ]);

        return back()->with('success', 'Карточка опубликована.');
    }

    public function reject(Request $request, Card $card)
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'rejection_reason.required' => 'Укажите причину отклонения.',
        ]);

        $card->update([
            'status' => 'rejected',
            'rejection_reason' => $data['rejection_reason'],
        ]);

        return back()->with('success', 'Карточка отклонена.');
    }
}
PHP,
    ],
    'routes/web.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

use App\Http\Controllers\AdminCardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CardController;
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
    Route::get('/cards', [CardController::class, 'index'])->name('cards.index');
    Route::get('/cards/create', [CardController::class, 'create'])->name('cards.create');
    Route::post('/cards', [CardController::class, 'store'])->name('cards.store');
    Route::delete('/cards/{card}', [CardController::class, 'destroy'])->name('cards.destroy');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminCardController::class, 'index'])->name('index');
    Route::get('/cards', [AdminCardController::class, 'index'])->name('cards.index');
    Route::post('/cards/{card}/publish', [AdminCardController::class, 'publish'])->name('cards.publish');
    Route::post('/cards/{card}/reject', [AdminCardController::class, 'reject'])->name('cards.reject');
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
    <title>@yield('title', 'Буквоежка')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<header class="top">
    <a class="brand" href="{{ route('home') }}">Буквоежка</a>
    <nav class="nav">
        @auth
            <a href="{{ route('cards.index') }}">Мои карточки</a>
            <a href="{{ route('cards.create') }}">Добавить книгу</a>
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

@section('title', 'Буквоежка — обмен книгами')

@section('content')
    <section class="hero">
        <span class="eyebrow">Демоэкзамен 09.02.07 · КИМ 2025</span>
        <h1>Обмен книгами</h1>
        <p>Добавьте книгу, которой готовы поделиться, или найдите ту, которую хотите получить. Карточки проходят модерацию.</p>
        <div class="actions">
            <a class="btn" href="{{ route('cards.create') }}">Добавить карточку</a>
            <a class="btn ghost" href="{{ route('register') }}">Зарегистрироваться</a>
        </div>
    </section>

    <section class="cards">
        <article class="card">
            <h2>Поделиться</h2>
            <p>У вас есть книга, которую можно отдать или обменять.</p>
        </article>
        <article class="card">
            <h2>Хочу в библиотеку</h2>
            <p>Вы ищете конкретную книгу и готовы забрать её.</p>
        </article>
        <article class="card">
            <h2>Модерация</h2>
            <p>Администратор публикует карточку или отклоняет её с причиной.</p>
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
    'resources/views/cards/index.blade.php' => [
        'language' => 'blade',
        'code' => <<<'BLADE'
@extends('layouts.app')

@section('title', 'Мои карточки')

@section('content')
    <section class="panel">
        <div class="panel-head">
            <h1>Мои карточки</h1>
            <a class="btn" href="{{ route('cards.create') }}">Добавить книгу</a>
        </div>

        @if ($cards->isEmpty())
            <p class="muted">Активных карточек нет.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Книга</th>
                            <th>Тип</th>
                            <th>Статус</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cards as $card)
                            <tr>
                                <td>
                                    <b>{{ $card->title }}</b><br>
                                    <span class="muted">{{ $card->author }}</span>
                                </td>
                                <td>{{ $card->type === 'share' ? 'Готов поделиться' : 'Хочу в библиотеку' }}</td>
                                <td><span class="status">{{ $card->status }}</span></td>
                                <td>
                                    <form method="POST" action="{{ route('cards.destroy', $card) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn small" type="submit">В архив</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <h2>Архив</h2>

        @if ($archive->isEmpty())
            <p class="muted">Архив пуст.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Книга</th>
                            <th>Статус</th>
                            <th>Причина</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($archive as $card)
                            <tr>
                                <td><b>{{ $card->title }}</b><br><span class="muted">{{ $card->author }}</span></td>
                                <td><span class="status">{{ $card->status }}</span></td>
                                <td>{{ $card->rejection_reason ?? '—' }}</td>
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
    'resources/views/cards/create.blade.php' => [
        'language' => 'blade',
        'code' => <<<'BLADE'
@extends('layouts.app')

@section('title', 'Новая карточка')

@section('content')
    <section class="panel narrow">
        <h1>Карточка книги</h1>

        <form method="POST" action="{{ route('cards.store') }}" novalidate>
            @csrf

            <div class="field">
                <label for="author">Автор книги</label>
                <input id="author" name="author" value="{{ old('author') }}" required>
                @error('author') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="title">Название книги</label>
                <input id="title" name="title" value="{{ old('title') }}" required>
                @error('title') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <fieldset class="field">
                <legend>Тип карточки</legend>
                <label class="radio">
                    <input type="radio" name="type" value="share" @checked(old('type') === 'share')>
                    Готов поделиться
                </label>
                <label class="radio">
                    <input type="radio" name="type" value="want" @checked(old('type') === 'want')>
                    Хочу в свою библиотеку
                </label>
                @error('type') <span class="error-text">{{ $message }}</span> @enderror
            </fieldset>

            <div class="field">
                <label for="publisher">Издательство</label>
                <input id="publisher" name="publisher" value="{{ old('publisher') }}">
            </div>

            <div class="field">
                <label for="year">Год издания</label>
                <input id="year" type="number" name="year" value="{{ old('year') }}" min="1800" max="2100">
            </div>

            <div class="field">
                <label for="binding">Переплёт</label>
                <select id="binding" name="binding">
                    <option value="">Не указано</option>
                    <option value="hard" @selected(old('binding') === 'hard')>Твёрдый</option>
                    <option value="soft" @selected(old('binding') === 'soft')>Мягкий</option>
                </select>
            </div>

            <div class="field">
                <label for="condition">Состояние</label>
                <select id="condition" name="condition">
                    <option value="">Не указано</option>
                    <option value="ideal" @selected(old('condition') === 'ideal')>Идеальное</option>
                    <option value="normal" @selected(old('condition') === 'normal')>Нормальное</option>
                    <option value="attention" @selected(old('condition') === 'attention')>Требует внимания</option>
                    <option value="leg" @selected(old('condition') === 'leg')>Годится подпирать ножку стола</option>
                </select>
            </div>

            <button class="btn" type="submit">Отправить карточку</button>
        </form>
    </section>
@endsection
BLADE,
    ],
    'resources/views/admin/cards/index.blade.php' => [
        'language' => 'blade',
        'code' => <<<'BLADE'
@extends('layouts.app')

@section('title', 'Админка — карточки')

@section('content')
    <section class="panel">
        <div class="panel-head">
            <h1>Все карточки</h1>
            <form method="GET" action="{{ route('admin.cards.index') }}" class="filter">
                <select name="status">
                    <option value="">Все статусы</option>
                    @foreach (['pending' => 'На рассмотрении', 'published' => 'Опубликована', 'rejected' => 'Отклонена', 'deleted' => 'Удалена'] as $key => $label)
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
                        <th>Книга</th>
                        <th>Пользователь</th>
                        <th>Тип</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cards as $card)
                        <tr>
                            <td><b>{{ $card->title }}</b><br><span class="muted">{{ $card->author }}</span></td>
                            <td>{{ $card->user->fio }}</td>
                            <td>{{ $card->type === 'share' ? 'Поделиться' : 'Хочу' }}</td>
                            <td>
                                <span class="status">{{ $card->status }}</span>
                                @if ($card->rejection_reason)
                                    <br><span class="muted">{{ $card->rejection_reason }}</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.cards.publish', $card) }}" class="status-form">
                                    @csrf
                                    <button class="btn small" type="submit">Опубликовать</button>
                                </form>
                                <form method="POST" action="{{ route('admin.cards.reject', $card) }}" class="status-form">
                                    @csrf
                                    <input name="rejection_reason" placeholder="Причина" required>
                                    <button class="btn small" type="submit">Отклонить</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="muted">Карточек нет.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $cards->links() }}</div>
    </section>
@endsection
BLADE,
    ],
    'tests/Feature/CardTest.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace Tests\Feature;

use App\Models\Card;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_cannot_open_cards(): void
    {
        $this->get('/cards')->assertRedirect('/login');
    }

    public function test_registration_creates_student(): void
    {
        $this->post('/register', [
            'fio' => 'Петров Пётр Петрович',
            'login' => 'НовыйЛогин',
            'phone' => '+7(900)-111-22-33',
            'email' => 'new@book.ru',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertRedirect('/cards');

        $this->assertDatabaseHas('users', [
            'login' => 'НовыйЛогин',
            'role' => 'student',
        ]);
    }

    public function test_student_can_create_card(): void
    {
        $student = User::where('login', 'student')->firstOrFail();

        $this->actingAs($student)->post('/cards', [
            'author' => 'Михаил Булгаков',
            'title' => 'Собачье сердце',
            'type' => 'share',
            'binding' => 'hard',
            'condition' => 'normal',
        ])->assertRedirect('/cards');

        $this->assertDatabaseHas('cards', [
            'user_id' => $student->id,
            'title' => 'Собачье сердце',
            'status' => 'pending',
        ]);
    }

    public function test_user_cannot_delete_someone_elses_card(): void
    {
        $owner = User::where('login', 'student')->firstOrFail();
        $other = User::create([
            'fio' => 'Другой Пользователь',
            'login' => 'ДругойЛогин',
            'phone' => '+7(900)-222-33-44',
            'email' => 'other@book.ru',
            'password' => Hash::make('secret123'),
            'role' => 'student',
        ]);

        $card = Card::where('user_id', $owner->id)->firstOrFail();

        $this->actingAs($other)->delete('/cards/'.$card->id)->assertForbidden();
    }

    public function test_admin_can_publish_and_reject(): void
    {
        $admin = User::where('login', 'admin')->firstOrFail();
        $card = Card::where('status', 'pending')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/cards/'.$card->id.'/publish')
            ->assertRedirect();

        $this->assertSame('published', $card->fresh()->status);

        $this->actingAs($admin)->post('/admin/cards/'.$card->id.'/reject', [
            'rejection_reason' => 'Нет издательства и состояния.',
        ])->assertRedirect();

        $this->assertSame('rejected', $card->fresh()->status);
        $this->assertSame('Нет издательства и состояния.', $card->fresh()->rejection_reason);
    }
}
PHP,
    ],
    'README.md' => [
        'language' => 'markdown',
        'code' => <<<'MD'
# Буквоежка — эталонный проект

Портал обмена книгами для демоэкзамена 09.02.07 (КИМ 2025).

## Запуск

```bash
composer create-project laravel/laravel bookexchange
cd bookexchange
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

- Администратор: `admin` / `bookworm`
- Студент: `student` / `Student2027!`

## Сценарий

Регистрация → вход → карточка книги → модерация → публикация или отклонение с причиной → архив.
MD,
    ],
];

$files['app/Http/Controllers/Controller.php'] = $base['app/Http/Controllers/Controller.php'];
$files['app/Providers/AppServiceProvider.php'] = $base['app/Providers/AppServiceProvider.php'];
$files['app/Http/Middleware/EnsureUserIsAdmin.php'] = $base['app/Http/Middleware/EnsureUserIsAdmin.php'];
$files['bootstrap/app.php'] = $base['bootstrap/app.php'];
$files['public/css/app.css'] = $base['public/css/app.css'];

return $files;
