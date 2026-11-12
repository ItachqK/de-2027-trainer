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
    'database/migrations/2026_01_01_000001_create_pizzas_table.php' => [
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
        Schema::create('pizzas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('price');
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pizzas');
    }
};
PHP,
    ],
    'database/migrations/2026_01_01_000002_create_orders_table.php' => [
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('address');
            $table->string('phone');
            $table->dateTime('delivery_at');
            $table->foreignId('pizza_id')->nullable()->constrained()->nullOnDelete();
            $table->string('custom_pizza')->nullable();
            $table->unsignedTinyInteger('quantity');
            $table->string('payment_type');
            $table->string('status')->default('new');
            $table->text('cancel_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
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

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
PHP,
    ],
    'app/Models/Pizza.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pizza extends Model
{
    protected $fillable = ['name', 'price', 'image'];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
PHP,
    ],
    'app/Models/Order.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'address',
        'phone',
        'delivery_at',
        'pizza_id',
        'custom_pizza',
        'quantity',
        'payment_type',
        'status',
        'cancel_reason',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pizza(): BelongsTo
    {
        return $this->belongsTo(Pizza::class);
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
            PizzaSeeder::class,
            OrderSeeder::class,
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
            ['login' => 'aDminka25'],
            [
                'fio' => 'Администратор доставки',
                'phone' => '+7(900)-000-00-01',
                'email' => 'admin@pizza.ru',
                'password' => Hash::make('passw0rd'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['login' => 'student'],
            [
                'fio' => 'Иванова Мария Сергеевна',
                'phone' => '+7(900)-000-00-02',
                'email' => 'student@pizza.ru',
                'password' => Hash::make('Student2027!'),
                'role' => 'student',
            ]
        );
    }
}
PHP,
    ],
    'database/seeders/PizzaSeeder.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace Database\Seeders;

use App\Models\Pizza;
use Illuminate\Database\Seeder;

class PizzaSeeder extends Seeder
{
    public function run(): void
    {
        $pizzas = [
            ['name' => 'Маргарита', 'price' => 450],
            ['name' => 'Пепперони', 'price' => 550],
            ['name' => 'Гавайская', 'price' => 590],
            ['name' => 'Четыре сыра', 'price' => 620],
            ['name' => 'Мясная', 'price' => 650],
            ['name' => 'Барбекю', 'price' => 600],
        ];

        foreach ($pizzas as $pizza) {
            Pizza::updateOrCreate(['name' => $pizza['name']], $pizza);
        }
    }
}
PHP,
    ],
    'database/seeders/OrderSeeder.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Pizza;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $student = User::where('login', 'student')->first();
        $pizza = Pizza::where('name', 'Маргарита')->first();

        if (! $student || ! $pizza) {
            return;
        }

        Order::updateOrCreate(
            ['user_id' => $student->id, 'address' => 'ул. Ленина, 10'],
            [
                'phone' => '+7(900)-000-00-02',
                'delivery_at' => now()->addDay()->setTime(19, 0),
                'pizza_id' => $pizza->id,
                'quantity' => 2,
                'payment_type' => 'card',
                'status' => 'new',
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
            'password' => ['required', 'regex:/^(?=.*[A-ZА-ЯЁ]).{6,}$/u', 'confirmed'],
        ], [
            'login.regex' => 'Логин: кириллица или цифры, не менее 6 символов.',
            'phone.regex' => 'Телефон в формате +7(XXX)-XXX-XX-XX.',
            'password.regex' => 'Пароль от 6 символов, хотя бы одна заглавная буква.',
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

        return redirect()->route('orders.index')->with('success', 'Регистрация прошла.');
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

        return redirect()->intended(route('orders.index'));
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
    'app/Http/Controllers/OrderController.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace App\Http\Controllers;

use App\Models\Pizza;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        return view('orders.index', [
            'orders' => $request->user()
                ->orders()
                ->with('pizza')
                ->latest()
                ->get(),
        ]);
    }

    public function create()
    {
        return view('order.create', [
            'pizzas' => Pizza::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^\+7\(\d{3}\)-\d{3}-\d{2}-\d{2}$/'],
            'delivery_at' => ['required', 'date', 'after:now'],
            'pizza_id' => ['nullable', 'integer', 'exists:pizzas,id'],
            'custom' => ['nullable', 'boolean'],
            'custom_pizza' => ['nullable', 'required_if:custom,1', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'between:1,20'],
            'payment_type' => ['required', 'in:cash,card'],
        ], [
            'delivery_at.after' => 'Дата и время должны быть в будущем.',
            'custom_pizza.required_if' => 'Опишите свою пиццу.',
            'quantity.between' => 'Количество — от 1 до 20.',
            'payment_type.in' => 'Выберите способ оплаты.',
        ]);

        if (! empty($data['custom'])) {
            $data['pizza_id'] = null;
        } else {
            $data['custom_pizza'] = null;
        }

        unset($data['custom']);

        $request->user()->orders()->create($data + ['status' => 'new']);

        return redirect()->route('orders.index')->with('success', 'Заказ принят.');
    }
}
PHP,
    ],
    'app/Http/Controllers/AdminOrderController.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $orders = Order::with(['user', 'pizza'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'status' => $status,
        ]);
    }

    public function status(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,on_the_way,delivered,cancelled'],
            'cancel_reason' => ['nullable', 'required_if:status,cancelled', 'string', 'max:500'],
        ], [
            'cancel_reason.required_if' => 'Укажите причину отмены.',
        ]);

        if ($data['status'] !== 'cancelled') {
            $data['cancel_reason'] = null;
        }

        $order->update($data);

        return back()->with('success', 'Статус заказа обновлён.');
    }
}
PHP,
    ],
    'routes/web.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
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
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/order', [OrderController::class, 'create'])->name('order.create');
    Route::post('/order', [OrderController::class, 'store'])->name('order.store');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminOrderController::class, 'index'])->name('index');
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/{order}/status', [AdminOrderController::class, 'status'])->name('orders.status');
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
    <title>@yield('title', 'Волшебная пицца')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<header class="top">
    <a class="brand" href="{{ route('home') }}">Волшебная пицца</a>
    <nav class="nav">
        @auth
            <a href="{{ route('orders.index') }}">Мои заказы</a>
            <a href="{{ route('order.create') }}">Заказать</a>
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

@section('title', 'Волшебная пицца — доставка')

@section('content')
    <section class="hero">
        <span class="eyebrow">Демоэкзамен 09.02.07 · КИМ 2025</span>
        <h1>Пицца с доставкой</h1>
        <p>Выберите пиццу из списка или опишите свою. Укажите адрес, время и оплату — администратор проведёт заказ по статусам.</p>
        <div class="actions">
            <a class="btn" href="{{ route('order.create') }}">Заказать</a>
            <a class="btn ghost" href="{{ route('register') }}">Зарегистрироваться</a>
        </div>
    </section>

    <section class="cards">
        <article class="card">
            <h2>6 видов пиццы</h2>
            <p>Маргарита, Пепперони, Гавайская, Четыре сыра, Мясная и Барбекю.</p>
        </article>
        <article class="card">
            <h2>Иная пицца</h2>
            <p>Если нужной нет в списке, отметьте чекбокс и опишите её.</p>
        </article>
        <article class="card">
            <h2>Статусы</h2>
            <p>Новый, в пути, доставлено или отменено с причиной.</p>
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
                <span class="muted">От 6 символов, хотя бы одна заглавная буква.</span>
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
    'resources/views/orders/index.blade.php' => [
        'language' => 'blade',
        'code' => <<<'BLADE'
@extends('layouts.app')

@section('title', 'Мои заказы')

@section('content')
    <section class="panel">
        <div class="panel-head">
            <h1>Мои заказы</h1>
            <a class="btn" href="{{ route('order.create') }}">Заказать</a>
        </div>

        @if ($orders->isEmpty())
            <p class="muted">Заказов пока нет.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Пицца</th>
                            <th>Количество</th>
                            <th>Адрес</th>
                            <th>Доставка</th>
                            <th>Оплата</th>
                            <th>Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td>{{ $order->pizza->name ?? $order->custom_pizza }}</td>
                                <td>{{ $order->quantity }}</td>
                                <td>{{ $order->address }}</td>
                                <td>{{ $order->delivery_at }}</td>
                                <td>{{ $order->payment_type }}</td>
                                <td>
                                    <span class="status">{{ $order->status }}</span>
                                    @if ($order->cancel_reason)
                                        <br><span class="muted">{{ $order->cancel_reason }}</span>
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
    'resources/views/order/create.blade.php' => [
        'language' => 'blade',
        'code' => <<<'BLADE'
@extends('layouts.app')

@section('title', 'Новый заказ')

@section('content')
    <section class="panel narrow">
        <h1>Заказ пиццы</h1>

        <form method="POST" action="{{ route('order.store') }}" novalidate>
            @csrf

            <div class="field">
                <label for="address">Адрес доставки</label>
                <input id="address" name="address" value="{{ old('address') }}" required>
                @error('address') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="phone">Телефон</label>
                <input id="phone" name="phone" value="{{ old('phone') }}" placeholder="+7(900)-000-00-00" required>
                @error('phone') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="delivery_at">Дата и время</label>
                <input id="delivery_at" type="datetime-local" name="delivery_at" value="{{ old('delivery_at') }}" required>
                @error('delivery_at') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="pizza_id">Пицца</label>
                <select id="pizza_id" name="pizza_id">
                    <option value="">Выберите пиццу</option>
                    @foreach ($pizzas as $pizza)
                        <option value="{{ $pizza->id }}" @selected(old('pizza_id') == $pizza->id)>
                            {{ $pizza->name }} · {{ $pizza->price }} ₽
                        </option>
                    @endforeach
                </select>
                @error('pizza_id') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label class="radio">
                    <input type="checkbox" name="custom" value="1" @checked(old('custom'))>
                    Иная пицца
                </label>
            </div>

            <div class="field">
                <label for="custom_pizza">Какая пицца</label>
                <input id="custom_pizza" name="custom_pizza" value="{{ old('custom_pizza') }}" placeholder="Опишите свою пиццу">
                @error('custom_pizza') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="quantity">Количество</label>
                <input id="quantity" type="number" name="quantity" min="1" max="20" value="{{ old('quantity', 1) }}" required>
                @error('quantity') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="payment_type">Оплата</label>
                <select id="payment_type" name="payment_type" required>
                    <option value="">Выберите способ</option>
                    <option value="cash" @selected(old('payment_type') === 'cash')>Наличные</option>
                    <option value="card" @selected(old('payment_type') === 'card')>Карта</option>
                </select>
                @error('payment_type') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <button class="btn" type="submit">Заказать</button>
        </form>
    </section>
@endsection
BLADE,
    ],
    'resources/views/admin/orders/index.blade.php' => [
        'language' => 'blade',
        'code' => <<<'BLADE'
@extends('layouts.app')

@section('title', 'Админка — заказы')

@section('content')
    <section class="panel">
        <div class="panel-head">
            <h1>Все заказы</h1>
            <form method="GET" action="{{ route('admin.orders.index') }}" class="filter">
                <select name="status">
                    <option value="">Все статусы</option>
                    @foreach (['new' => 'Новый', 'on_the_way' => 'В пути', 'delivered' => 'Доставлено', 'cancelled' => 'Отменено'] as $key => $label)
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
                        <th>Клиент</th>
                        <th>Пицца</th>
                        <th>Кол-во</th>
                        <th>Адрес</th>
                        <th>Доставка</th>
                        <th>Статус</th>
                        <th>Изменить</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->user->fio }}<br><span class="muted">{{ $order->phone }}</span></td>
                            <td>{{ $order->pizza->name ?? $order->custom_pizza }}</td>
                            <td>{{ $order->quantity }}</td>
                            <td>{{ $order->address }}</td>
                            <td>{{ $order->delivery_at }}</td>
                            <td>
                                <span class="status">{{ $order->status }}</span>
                                @if ($order->cancel_reason)
                                    <br><span class="muted">{{ $order->cancel_reason }}</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="status-form">
                                    @csrf
                                    <select name="status">
                                        @foreach (['new' => 'Новый', 'on_the_way' => 'В пути', 'delivered' => 'Доставлено', 'cancelled' => 'Отменено'] as $key => $label)
                                            <option value="{{ $key }}" @selected($order->status === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <input name="cancel_reason" placeholder="Причина отмены">
                                    <button class="btn small" type="submit">Сохранить</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="muted">Заказов нет.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $orders->links() }}</div>
    </section>
@endsection
BLADE,
    ],
    'tests/Feature/OrderTest.php' => [
        'language' => 'php',
        'code' => <<<'PHP'
<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Pizza;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_cannot_open_orders(): void
    {
        $this->get('/orders')->assertRedirect('/login');
    }

    public function test_registration_creates_student(): void
    {
        $this->post('/register', [
            'fio' => 'Петров Пётр Петрович',
            'login' => 'НовыйЛогин',
            'phone' => '+7(900)-111-22-33',
            'email' => 'new@pizza.ru',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
        ])->assertRedirect('/orders');

        $this->assertDatabaseHas('users', [
            'login' => 'НовыйЛогин',
            'role' => 'student',
        ]);
    }

    public function test_student_can_create_order(): void
    {
        $student = User::where('login', 'student')->firstOrFail();
        $pizza = Pizza::firstOrFail();

        $this->actingAs($student)->post('/order', [
            'address' => 'ул. Пушкина, 1',
            'phone' => '+7(900)-111-22-33',
            'delivery_at' => now()->addHour()->format('Y-m-d\TH:i'),
            'pizza_id' => $pizza->id,
            'quantity' => 2,
            'payment_type' => 'card',
        ])->assertRedirect('/orders');

        $this->assertDatabaseHas('orders', [
            'user_id' => $student->id,
            'pizza_id' => $pizza->id,
            'quantity' => 2,
            'status' => 'new',
        ]);
    }

    public function test_custom_pizza_requires_description(): void
    {
        $student = User::where('login', 'student')->firstOrFail();

        $this->actingAs($student)->post('/order', [
            'address' => 'ул. Пушкина, 1',
            'phone' => '+7(900)-111-22-33',
            'delivery_at' => now()->addHour()->format('Y-m-d\TH:i'),
            'custom' => 1,
            'quantity' => 1,
            'payment_type' => 'cash',
        ])->assertSessionHasErrors('custom_pizza');
    }

    public function test_admin_can_cancel_with_reason(): void
    {
        $admin = User::where('login', 'aDminka25')->firstOrFail();
        $order = Order::firstOrFail();

        $this->actingAs($admin)->post('/admin/orders/'.$order->id.'/status', [
            'status' => 'cancelled',
            'cancel_reason' => 'Клиент не отвечает.',
        ])->assertRedirect();

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame('Клиент не отвечает.', $order->fresh()->cancel_reason);
    }

    public function test_admin_cannot_cancel_without_reason(): void
    {
        $admin = User::where('login', 'aDminka25')->firstOrFail();
        $order = Order::firstOrFail();

        $this->actingAs($admin)->post('/admin/orders/'.$order->id.'/status', [
            'status' => 'cancelled',
        ])->assertSessionHasErrors('cancel_reason');
    }
}
PHP,
    ],
    'README.md' => [
        'language' => 'markdown',
        'code' => <<<'MD'
# Волшебная пицца — эталонный проект

Доставка пиццы для демоэкзамена 09.02.07 (КИМ 2025).

## Запуск

```bash
composer create-project laravel/laravel pizza
cd pizza
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

- Администратор: `aDminka25` / `passw0rd`
- Студент: `student` / `Student2027!`

## Сценарий

Регистрация → вход → заказ пиццы → админка → «В пути» / «Доставлено» / «Отменено» с причиной.
MD,
    ],
];

$files['app/Http/Controllers/Controller.php'] = $base['app/Http/Controllers/Controller.php'];
$files['app/Providers/AppServiceProvider.php'] = $base['app/Providers/AppServiceProvider.php'];
$files['app/Http/Middleware/EnsureUserIsAdmin.php'] = $base['app/Http/Middleware/EnsureUserIsAdmin.php'];
$files['bootstrap/app.php'] = $base['bootstrap/app.php'];
$files['public/css/app.css'] = $base['public/css/app.css'];

return $files;
