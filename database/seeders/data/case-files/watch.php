<?php

// Код проекта «Точный ход» для разбора. Сгенерировано из рабочего проекта.

return [
    'app/Providers/AppServiceProvider.php' => [
        'language' => 'php',
        'code' => <<<'CODE'
<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));

        Paginator::defaultView('pagination.custom');
        Paginator::defaultSimpleView('pagination.custom');
    }
}
CODE,
    ],
    'bootstrap/app.php' => [
        'language' => 'php',
        'code' => <<<'CODE'
<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
CODE,
    ],
    '.env.example' => [
        'language' => 'text',
        'code' => <<<'CODE'
APP_NAME="Точный ход"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8200
APP_TIMEZONE=Asia/Yekaterinburg
APP_LOCALE=ru
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=ru_RU

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=sqlite

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
CODE,
    ],
    'database/migrations/0001_01_01_000000_create_users_table.php' => [
        'language' => 'php',
        'code' => <<<'CODE'
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
            $table->string('fio', 120);
            $table->string('login', 60)->unique();
            $table->string('phone', 20);
            $table->string('email', 120)->unique();
            $table->string('password');
            $table->enum('role', ['user', 'admin'])->default('user');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
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
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
CODE,
    ],
    'database/migrations/2026_11_12_000001_create_watches_table.php' => [
        'language' => 'php',
        'code' => <<<'CODE'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watches', function (Blueprint $table) {
            $table->id();
            $table->string('brand', 60);
            $table->string('model', 80);
            $table->string('slug', 80)->unique();
            $table->unsignedInteger('price');
            $table->unsignedSmallInteger('case_mm');
            $table->enum('movement', ['quartz', 'mechanical', 'automatic']);
            $table->unsignedSmallInteger('stock')->default(0);
            $table->string('dial', 20)->default('graphite');
            $table->string('summary', 160);
            $table->text('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watches');
    }
};
CODE,
    ],
    'database/migrations/2026_11_12_000002_create_orders_table.php' => [
        'language' => 'php',
        'code' => <<<'CODE'
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
            $table->foreignId('watch_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('quantity')->default(1);
            $table->unsignedInteger('total');
            $table->date('pickup_date');
            $table->enum('payment', ['onsite', 'sbp']);
            $table->enum('status', ['new', 'ready', 'issued'])->default('new');
            $table->string('comment', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
CODE,
    ],
    'database/migrations/2026_11_12_000003_create_reviews_table.php' => [
        'language' => 'php',
        'code' => <<<'CODE'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('text', 600);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
CODE,
    ],
    'app/Models/User.php' => [
        'language' => 'php',
        'code' => <<<'CODE'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'fio',
        'login',
        'phone',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function firstName(): string
    {
        return explode(' ', trim($this->fio))[1] ?? $this->fio;
    }
}
CODE,
    ],
    'app/Models/Watch.php' => [
        'language' => 'php',
        'code' => <<<'CODE'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Watch extends Model
{
    protected $fillable = [
        'brand',
        'model',
        'slug',
        'price',
        'case_mm',
        'movement',
        'stock',
        'dial',
        'summary',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'case_mm' => 'integer',
            'stock' => 'integer',
        ];
    }

    public const MOVEMENTS = [
        'quartz' => 'Кварцевый',
        'mechanical' => 'Механический',
        'automatic' => 'Автоподзавод',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(Review::class, Order::class);
    }

    public function movementLabel(): string
    {
        return self::MOVEMENTS[$this->movement] ?? $this->movement;
    }

    public function priceLabel(): string
    {
        return number_format($this->price, 0, ',', ' ').' ₽';
    }

    public function isAvailable(): bool
    {
        return $this->stock > 0;
    }
}
CODE,
    ],
    'app/Models/Order.php' => [
        'language' => 'php',
        'code' => <<<'CODE'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'watch_id',
        'quantity',
        'total',
        'pickup_date',
        'payment',
        'status',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'total' => 'integer',
            'pickup_date' => 'date',
        ];
    }

    public const STATUSES = [
        'new' => 'Новый',
        'ready' => 'Готов к выдаче',
        'issued' => 'Выдан',
    ];

    public const PAYMENTS = [
        'onsite' => 'При получении',
        'sbp' => 'Переводом по СБП',
    ];

    /**
     * Разрешённые переходы. Шаг назад и прыжок через статус запрещены,
     * чтобы в базе не появлялись «выданные» заказы без сборки.
     */
    public const TRANSITIONS = [
        'new' => ['ready'],
        'ready' => ['issued'],
        'issued' => [],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function watch(): BelongsTo
    {
        return $this->belongsTo(Watch::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function paymentLabel(): string
    {
        return self::PAYMENTS[$this->payment] ?? $this->payment;
    }

    public function canMoveTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    public function totalLabel(): string
    {
        return number_format($this->total, 0, ',', ' ').' ₽';
    }

    public function isIssued(): bool
    {
        return $this->status === 'issued';
    }

    public function canBeReviewed(): bool
    {
        return $this->isIssued() && $this->review === null;
    }
}
CODE,
    ],
    'app/Models/Review.php' => [
        'language' => 'php',
        'code' => <<<'CODE'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'rating',
        'text',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
CODE,
    ],
    'database/seeders/DatabaseSeeder.php' => [
        'language' => 'php',
        'code' => <<<'CODE'
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            WatchSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
CODE,
    ],
    'database/seeders/UserSeeder.php' => [
        'language' => 'php',
        'code' => <<<'CODE'
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'fio' => 'Верещагин Алексей Петрович',
                'login' => 'TikTak2027',
                'phone' => '8(343)200-18-14',
                'email' => 'admin@tochny-hod.ru',
                'password' => 'Demo77',
                'role' => 'admin',
            ],
            [
                'fio' => 'Иванова Мария Сергеевна',
                'login' => 'maria2027',
                'phone' => '8(912)345-67-89',
                'email' => 'maria@example.ru',
                'password' => 'Student2027!',
                'role' => 'user',
            ],
            [
                'fio' => 'Соколов Дмитрий Андреевич',
                'login' => 'dmitry2027',
                'phone' => '8(922)118-40-23',
                'email' => 'dmitry@example.ru',
                'password' => 'Student2027!',
                'role' => 'user',
            ],
            [
                'fio' => 'Кузнецова Ольга Игоревна',
                'login' => 'olga2027',
                'phone' => '8(950)774-02-16',
                'email' => 'olga@example.ru',
                'password' => 'Student2027!',
                'role' => 'user',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(['login' => $user['login']], $user);
        }
    }
}
CODE,
    ],
    'database/seeders/WatchSeeder.php' => [
        'language' => 'php',
        'code' => <<<'CODE'
<?php

namespace Database\Seeders;

use App\Models\Watch;
use Illuminate\Database\Seeder;

class WatchSeeder extends Seeder
{
    public function run(): void
    {
        $watches = [
            [
                'brand' => 'Точный ход',
                'model' => 'Стрелка 01',
                'slug' => 'strella-01',
                'price' => 12900,
                'case_mm' => 38,
                'movement' => 'quartz',
                'stock' => 6,
                'dial' => 'graphite',
                'summary' => 'Три стрелки, сапфировое стекло, ремешок из телячьей кожи',
                'description' => 'Базовая модель салона: кварцевый механизм, корпус 38 мм, водозащита 50 м. Подходит на каждый день и на узкое запястье.',
            ],
            [
                'brand' => 'Точный ход',
                'model' => 'Полночь',
                'slug' => 'polnoch',
                'price' => 48500,
                'case_mm' => 40,
                'movement' => 'automatic',
                'stock' => 3,
                'dial' => 'midnight',
                'summary' => 'Автоподзавод, запас хода 42 часа, стальной браслет',
                'description' => 'Синий циферблат с накладными метками, механизм с автоподзаводом, задняя крышка из сапфира. Запас хода — 42 часа.',
            ],
            [
                'brand' => 'Точный ход',
                'model' => 'Маяк',
                'slug' => 'mayak',
                'price' => 26400,
                'case_mm' => 42,
                'movement' => 'mechanical',
                'stock' => 4,
                'dial' => 'cream',
                'summary' => 'Ручной завод, светящиеся метки, ремешок NATO',
                'description' => 'Механика с ручным заводом и запасом хода 38 часов. Метки и стрелки покрыты люминофором, ремешок NATO меняется без инструментов.',
            ],
            [
                'brand' => 'Точный ход',
                'model' => 'Компас',
                'slug' => 'kompas',
                'price' => 15700,
                'case_mm' => 40,
                'movement' => 'quartz',
                'stock' => 5,
                'dial' => 'olive',
                'summary' => 'Двойное время, компас на безеле, водозащита 100 м',
                'description' => 'Кварцевые часы для поездок: второй часовой пояс, вращающийся безель с компасом, водозащита 100 м.',
            ],
            [
                'brand' => 'Точный ход',
                'model' => 'Рубеж',
                'slug' => 'rubezh',
                'price' => 62000,
                'case_mm' => 44,
                'movement' => 'automatic',
                'stock' => 2,
                'dial' => 'black',
                'summary' => 'Хронограф, 100 м, браслет с литым замком',
                'description' => 'Хронограф с автоподзаводом, корпус 44 мм, кнопки с завинчиванием, водозащита 100 м. Самая крупная модель витрины.',
            ],
            [
                'brand' => 'Точный ход',
                'model' => 'Тишина',
                'slug' => 'tishina',
                'price' => 9800,
                'case_mm' => 36,
                'movement' => 'quartz',
                'stock' => 8,
                'dial' => 'sand',
                'summary' => 'Минимализм без секундной стрелки, корпус 36 мм',
                'description' => 'Только часы и минуты: чистая разметка, песочный циферблат, тонкий корпус 36 мм. Самый лёгкий вариант на каждый день.',
            ],
        ];

        foreach ($watches as $watch) {
            Watch::updateOrCreate(['slug' => $watch['slug']], $watch);
        }
    }
}
CODE,
    ],
    'database/seeders/OrderSeeder.php' => [
        'language' => 'php',
        'code' => <<<'CODE'
<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use App\Models\Watch;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        if (Order::exists()) {
            return;
        }

        $maria = User::where('login', 'maria2027')->first();
        $dmitry = User::where('login', 'dmitry2027')->first();
        $olga = User::where('login', 'olga2027')->first();
        $polnoch = Watch::where('slug', 'polnoch')->first();
        $strella = Watch::where('slug', 'strella-01')->first();
        $rubezh = Watch::where('slug', 'rubezh')->first();
        $kompas = Watch::where('slug', 'kompas')->first();
        $mayak = Watch::where('slug', 'mayak')->first();

        if (! $maria || ! $dmitry || ! $olga || ! $polnoch || ! $strella || ! $rubezh || ! $kompas || ! $mayak) {
            return;
        }

        $issued = Order::create([
            'user_id' => $maria->id,
            'watch_id' => $polnoch->id,
            'quantity' => 1,
            'total' => $polnoch->price,
            'pickup_date' => now()->subDays(9)->toDateString(),
            'payment' => 'sbp',
            'status' => 'issued',
            'comment' => 'Подогнать браслет под запястье 16,5 см.',
            'created_at' => now()->subDays(12),
            'updated_at' => now()->subDays(9),
        ]);

        Review::create([
            'order_id' => $issued->id,
            'user_id' => $maria->id,
            'rating' => 5,
            'text' => 'Брала «Полночь» на юбилей мужу. Браслет подогнали за пять минут, ход проверили при мне, за две недели ушли на четыре секунды. Сервис спокойный, без навязывания.',
        ]);

        Order::create([
            'user_id' => $maria->id,
            'watch_id' => $strella->id,
            'quantity' => 2,
            'total' => $strella->price * 2,
            'pickup_date' => now()->addDays(2)->toDateString(),
            'payment' => 'onsite',
            'status' => 'ready',
            'comment' => null,
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDay(),
        ]);

        Order::create([
            'user_id' => $dmitry->id,
            'watch_id' => $rubezh->id,
            'quantity' => 1,
            'total' => $rubezh->price,
            'pickup_date' => now()->addDays(5)->toDateString(),
            'payment' => 'onsite',
            'status' => 'new',
            'comment' => 'Нужна примерка на запястье 19 см.',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $kompasIssued = Order::create([
            'user_id' => $dmitry->id,
            'watch_id' => $kompas->id,
            'quantity' => 1,
            'total' => $kompas->price,
            'pickup_date' => now()->subDays(20)->toDateString(),
            'payment' => 'onsite',
            'status' => 'issued',
            'comment' => null,
            'created_at' => now()->subDays(24),
            'updated_at' => now()->subDays(20),
        ]);

        Review::create([
            'order_id' => $kompasIssued->id,
            'user_id' => $dmitry->id,
            'rating' => 4,
            'text' => 'Брал «Компас» для поездок: второй часовой пояс выставляется за секунду, безель не люфтит. За месяц точность не ушла. Единственное — ремешок жёсткий, сразу поменял на NATO.',
        ]);

        $mayakIssued = Order::create([
            'user_id' => $olga->id,
            'watch_id' => $mayak->id,
            'quantity' => 1,
            'total' => $mayak->price,
            'pickup_date' => now()->subDays(5)->toDateString(),
            'payment' => 'sbp',
            'status' => 'issued',
            'comment' => 'Подарочная упаковка.',
            'created_at' => now()->subDays(9),
            'updated_at' => now()->subDays(5),
        ]);

        Review::create([
            'order_id' => $mayakIssued->id,
            'user_id' => $olga->id,
            'rating' => 5,
            'text' => 'Понравилось, что не пытались продать модель дороже. Показали три варианта, дали походить по салону. «Маяк» заводится мягко, люминофор светит всю ночь.',
        ]);
    }
}
CODE,
    ],
    'app/Http/Controllers/AuthController.php' => [
        'language' => 'php',
        'code' => <<<'CODE'
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

        return redirect()->route('orders.index')
            ->with('success', 'Аккаунт создан. Выберите часы в каталоге.');
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

        return redirect()->intended(route('orders.index'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('catalog')->with('success', 'Вы вышли из аккаунта.');
    }
}
CODE,
    ],
    'app/Http/Middleware/EnsureUserIsAdmin.php' => [
        'language' => 'php',
        'code' => <<<'CODE'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'Раздел доступен только администратору.');
        }

        return $next($request);
    }
}
CODE,
    ],
    'resources/views/auth/register.blade.php' => [
        'language' => 'blade',
        'code' => <<<'CODE'
@extends('layouts.app')

@section('title', 'Регистрация — Точный ход')

@section('content')
  <section class="auth wrap">
    <div class="auth-panel auth-panel--dark">
      <p class="auth-quote">«Часы выбирают один раз и на десять лет. Поэтому мы даём примерить все шесть».</p>
      <p class="auth-quote-by">Салон «Точный ход», Малышева, 12</p>
      <ul class="auth-list">
        <li>Гарантия два года на механизм</li>
        <li>Бесплатная подгонка браслета</li>
        <li>Обмен в течение 14 дней</li>
      </ul>
    </div>

    <div class="auth-form">
      <h1>Создание аккаунта</h1>
      <p class="auth-sub">После регистрации вы сразу попадёте в свои заказы.</p>

      @if ($errors->any())
        <div class="alert alert--error" role="alert">
          @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
          @endforeach
        </div>
      @endif

      <form method="POST" action="{{ route('register') }}" class="form">
        @csrf

        <div class="field">
          <label for="fio">Фамилия, имя, отчество</label>
          <input type="text" id="fio" name="fio" value="{{ old('fio') }}" required autofocus
                 autocomplete="name" placeholder="Иванова Мария Сергеевна"
                 @error('fio') aria-invalid="true" @enderror>
          <span class="hint">Только кириллица, пробелы и дефис.</span>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="login">Логин</label>
            <input type="text" id="login" name="login" value="{{ old('login') }}" required
                   autocomplete="username" placeholder="maria2027"
                   @error('login') aria-invalid="true" @enderror>
            <span class="hint">Латиница и цифры, от 6 символов.</span>
          </div>

          <div class="field">
            <label for="phone">Телефон</label>
            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required
                   autocomplete="tel" placeholder="8(912)345-67-89"
                   @error('phone') aria-invalid="true" @enderror>
            <span class="hint">Формат 8(XXX)XXX-XX-XX.</span>
          </div>
        </div>

        <div class="field">
          <label for="email">Электронная почта</label>
          <input type="email" id="email" name="email" value="{{ old('email') }}" required
                 autocomplete="email" placeholder="maria@example.ru"
                 @error('email') aria-invalid="true" @enderror>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="password">Пароль</label>
            <input type="password" id="password" name="password" required autocomplete="new-password"
                   @error('password') aria-invalid="true" @enderror>
            <span class="hint">От 8 символов, хотя бы одна буква и цифра.</span>
          </div>

          <div class="field">
            <label for="password_confirmation">Пароль ещё раз</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required
                   autocomplete="new-password">
          </div>
        </div>

        <button type="submit" class="btn btn-block">Создать аккаунт</button>
      </form>

      <p class="auth-alt">Уже есть аккаунт? <a href="{{ route('login') }}">Войти</a></p>
    </div>
  </section>
@endsection
CODE,
    ],
    'resources/views/auth/login.blade.php' => [
        'language' => 'blade',
        'code' => <<<'CODE'
@extends('layouts.app')

@section('title', 'Вход — Точный ход')

@section('content')
  <section class="auth wrap">
    <div class="auth-panel">
      <p class="auth-quote">«Хорошие часы не торопят и не отстают. Просто идут».</p>
      <p class="auth-quote-by">Алексей Верещагин, часовой мастер салона</p>
      <div class="auth-panel-clock">
        @include('partials.dial', ['dial' => 'cream', 'brand' => 'ТЧ', 'label' => 'Часы салона'])
      </div>
    </div>

    <div class="auth-form">
      <h1>Вход в аккаунт</h1>
      <p class="auth-sub">Чтобы посмотреть свои заказы и статусы, войдите под логином и паролем.</p>

      @if ($errors->any())
        <div class="alert alert--error" role="alert">
          @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
          @endforeach
        </div>
      @endif

      <form method="POST" action="{{ route('login') }}" class="form">
        @csrf

        <div class="field">
          <label for="login">Логин</label>
          <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                 autocomplete="username" @error('login') aria-invalid="true" @enderror>
        </div>

        <div class="field">
          <label for="password">Пароль</label>
          <input type="password" id="password" name="password" required autocomplete="current-password"
                 @error('password') aria-invalid="true" @enderror>
        </div>

        <label class="check">
          <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
          <span>Запомнить меня на этом компьютере</span>
        </label>

        <button type="submit" class="btn btn-block">Войти</button>
      </form>

      <p class="auth-alt">Ещё не зарегистрированы? <a href="{{ route('register') }}">Создайте аккаунт</a></p>
    </div>
  </section>
@endsection
CODE,
    ],
    'resources/views/layouts/app.blade.php' => [
        'language' => 'blade',
        'code' => <<<'CODE'
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Точный ход — часовой салон')</title>
  <meta name="description" content="Часовой салон «Точный ход»: шесть моделей в наличии, примерка в салоне, гарантия 2 года. Учебный проект по КИМ 09.02.07.">
  <meta property="og:title" content="Точный ход — часовой салон">
  <meta property="og:description" content="Механика, кварц и автоподзавод. Заказ, статусы и отзывы — учебный проект на Laravel.">
  <meta property="og:type" content="website">
  <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
  <link rel="stylesheet" href="{{ asset('fonts/fonts.css') }}">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
  <a class="skip-link" href="#main">Перейти к содержанию</a>

  <header class="site-header">
    <div class="wrap header-inner">
      <a class="brand" href="{{ route('catalog') }}">
        <span class="brand-mark" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <circle cx="12" cy="12" r="8.5"/>
            <path d="M12 7.5V12l3 2"/>
          </svg>
        </span>
        <span class="brand-text">
          <strong>Точный ход</strong>
          <span>часовой салон</span>
        </span>
      </a>

      <nav class="site-nav" aria-label="Основная навигация">
        <a href="{{ route('catalog') }}" class="{{ request()->routeIs('catalog') ? 'is-active' : '' }}">Каталог</a>
        @auth
          <a href="{{ route('orders.index') }}" class="{{ request()->routeIs('orders.*') ? 'is-active' : '' }}">Мои заказы</a>
          @if (auth()->user()->isAdmin())
            <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.*') ? 'is-active' : '' }}">Админка</a>
          @endif
        @endauth
      </nav>

      <div class="header-user">
        @auth
          <span class="who">{{ auth()->user()->firstName() }}</span>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="link-btn">Выйти</button>
          </form>
        @else
          <a class="link-btn" href="{{ route('login') }}">Войти</a>
          <a class="btn btn-small" href="{{ route('register') }}">Регистрация</a>
        @endauth
      </div>
    </div>
  </header>

  <main id="main">
    @if (session('success'))
      <div class="wrap">
        <div class="alert alert--success" role="status">{{ session('success') }}</div>
      </div>
    @endif

    @yield('content')
  </main>

  <footer class="site-footer">
    <div class="wrap footer-inner">
      <div class="footer-brand">
        <strong>Точный ход</strong>
        <p>Екатеринбург, Малышева, 12. Ежедневно с 10:00 до 21:00.<br>+7 (343) 200-18-14</p>
      </div>
      <div class="footer-note">
        <p>Учебный проект по КИМ 09.02.07-3-2027. Названия моделей, цены и отзывы — демонстрационные.</p>
        <p>Laravel · SQLite · Blade</p>
      </div>
    </div>
  </footer>

  <script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
CODE,
    ],
    'resources/views/catalog.blade.php' => [
        'language' => 'blade',
        'code' => <<<'CODE'
@extends('layouts.app')

@section('title', 'Точный ход — часы в наличии')

@section('content')
  <section class="hero">
    <div class="wrap hero-grid">
      <div class="hero-copy">
        <p class="hero-kicker">Часовой салон в Екатеринбурге, работаем с 2014 года</p>
        <h1>Часы, которые переживут тренды</h1>
        <p class="lead">
          Шесть моделей в наличии: кварц, механика и автоподзавод. Каждую пару проверяем
          на приборе и отдаём с гарантией два года. Примерка — в салоне на Малышева, 12.
        </p>
        <div class="hero-actions">
          <a class="btn" href="#catalog">Выбрать часы</a>
          <a class="link-arrow" href="#service">Как мы проверяем</a>
        </div>
        <dl class="hero-facts">
          <div><dt>Моделей в наличии</dt><dd>6</dd></div>
          <div><dt>Гарантия</dt><dd>2 года</dd></div>
          <div><dt>Примерка</dt><dd>без записи</dd></div>
        </dl>
      </div>

      <aside class="clock-card" aria-label="Текущее время салона">
        <div class="clock-top">
          <span>Сейчас в салоне</span>
          <span class="clock-live"><i></i> идут</span>
        </div>
        <svg class="clock" viewBox="0 0 200 200" aria-hidden="true">
          <circle cx="100" cy="100" r="92" fill="#F4EFE3"/>
          <circle cx="100" cy="100" r="92" fill="none" stroke="#A6772C" stroke-width="2"/>
          @for ($i = 0; $i < 60; $i++)
            <line x1="100" y1="14" x2="100" y2="{{ $i % 5 === 0 ? 24 : 19 }}"
                  stroke="#2A2521" stroke-width="{{ $i % 5 === 0 ? 2 : 0.8 }}"
                  opacity="{{ $i % 5 === 0 ? 0.85 : 0.35 }}"
                  transform="rotate({{ $i * 6 }} 100 100)"/>
          @endfor
          <text x="100" y="56" text-anchor="middle" fill="#2A2521" font-size="9" letter-spacing="3" opacity=".7">ТОЧНЫЙ ХОД</text>
          <g id="clock-hand-hour" transform="rotate(300 100 100)">
            <line x1="100" y1="100" x2="100" y2="52" stroke="#2A2521" stroke-width="5" stroke-linecap="round"/>
          </g>
          <g id="clock-hand-minute" transform="rotate(60 100 100)">
            <line x1="100" y1="100" x2="100" y2="34" stroke="#2A2521" stroke-width="3" stroke-linecap="round"/>
          </g>
          <g id="clock-hand-second">
            <line id="clock-second-line" x1="100" y1="112" x2="100" y2="26" stroke="#A6772C" stroke-width="1.6" stroke-linecap="round"/>
          </g>
          <circle cx="100" cy="100" r="4" fill="#A6772C"/>
        </svg>
        <div class="clock-digital">
          <span id="clock-time">--:--</span>
          <span class="clock-seconds" id="clock-seconds">00</span>
        </div>
        <div class="seconds-rail" id="seconds-rail" aria-hidden="true">
          @for ($i = 0; $i < 60; $i++)
            <i></i>
          @endfor
        </div>
        <p class="clock-note">Малышева, 12 · ежедневно 10:00–21:00</p>
      </aside>
    </div>
  </section>

  <section class="catalog wrap" id="catalog">
    <header class="section-head">
      <h2>Что стоит на витрине</h2>
      <p>Цены — за одну пару. В наличии указано, сколько штук осталось на складе салона.</p>
    </header>

    @guest
      <div class="notice">
        <p>Оформить заказ можно после входа. <a href="{{ route('register') }}">Создайте аккаунт</a> — это займёт минуту.</p>
      </div>
    @endguest

    <div class="watch-list">
      @foreach ($watches as $watch)
        <article class="watch-row">
          @include('partials.dial', ['dial' => $watch->dial, 'brand' => 'ТЧ', 'label' => $watch->brand.' '.$watch->model])

          <div class="watch-main">
            <h3>{{ $watch->brand }} <span>{{ $watch->model }}</span></h3>
            <p>{{ $watch->summary }}</p>
            @if ($watch->reviews_count > 0)
              <p class="watch-rating">
                <span class="stars" aria-hidden="true">{{ str_repeat('★', (int) round($watch->reviews_avg_rating)) }}{{ str_repeat('☆', 5 - (int) round($watch->reviews_avg_rating)) }}</span>
                {{ number_format($watch->reviews_avg_rating, 1, ',', '') }} · {{ $watch->reviews_count }} {{ \App\Support\Text::plural($watch->reviews_count, 'отзыв', 'отзыва', 'отзывов') }}
              </p>
            @endif
          </div>

          <dl class="watch-specs">
            <div><dt>Корпус</dt><dd>{{ $watch->case_mm }} мм</dd></div>
            <div><dt>Механизм</dt><dd>{{ $watch->movementLabel() }}</dd></div>
            <div><dt>Наличие</dt><dd>{{ $watch->stock > 0 ? $watch->stock.' шт.' : 'нет' }}</dd></div>
          </dl>

          <div class="watch-buy">
            <b class="price">{{ $watch->priceLabel() }}</b>
            @if ($watch->isAvailable())
              <a class="btn btn-small" href="{{ route('orders.create', ['watch' => $watch->id]) }}">Заказать</a>
            @else
              <span class="btn btn-small is-disabled" aria-disabled="true">Нет в наличии</span>
            @endif
          </div>
        </article>
      @endforeach
    </div>
  </section>

  <section class="service" id="service">
    <div class="wrap">
      <header class="section-head">
        <h2>Перед выдачей</h2>
        <p>Часы не уходят из салона, пока мастер не проверит ход и точность.</p>
      </header>
      <ol class="service-list">
        <li><b>01</b><div><h3>Проверка на приборе</h3><p>Замер амплитуды и суточного хода, при необходимости — регулировка.</p></div></li>
        <li><b>02</b><div><h3>Комплект и документы</h3><p>Паспорт, гарантийный талон на два года, коробка и салфетка.</p></div></li>
        <li><b>03</b><div><h3>Примерка и подгонка</h3><p>Укорачиваем браслет по запястью бесплатно, при вас.</p></div></li>
        <li><b>04</b><div><h3>Обмен 14 дней</h3><p>Если модель не подошла и нет следов носки — меняем или возвращаем деньги.</p></div></li>
      </ol>
    </div>
  </section>

  @if ($reviews->isNotEmpty())
    <section class="reviews wrap">
      <header class="section-head">
        <h2>Что говорят владельцы</h2>
        <p>Отзывы оставляют только те, кто уже забрал заказ из салона.</p>
      </header>
      <div class="review-grid">
        @foreach ($reviews as $review)
          <figure class="review">
            <div class="review-head">
              <span class="stars" aria-label="Оценка {{ $review->rating }} из 5">{{ str_repeat('★', $review->rating) }}<span class="stars-empty">{{ str_repeat('☆', 5 - $review->rating) }}</span></span>
              <time datetime="{{ $review->created_at->toDateString() }}">{{ $review->created_at->translatedFormat('j F') }}</time>
            </div>
            <blockquote>{{ $review->text }}</blockquote>
            <figcaption>
              {{ $review->user->fio }}
              <span>{{ $review->order->watch->brand }} {{ $review->order->watch->model }}</span>
            </figcaption>
          </figure>
        @endforeach
      </div>
    </section>
  @endif

  <section class="cta wrap">
    <div class="cta-inner">
      <div>
        <h2>Не знаете, что выбрать?</h2>
        <p>Приходите на примерку: покажем все шесть моделей, дадим поносить и сравнить на руке.</p>
      </div>
      <a class="btn btn-light" href="tel:+73432001814">+7 (343) 200-18-14</a>
    </div>
  </section>
@endsection
CODE,
    ],
    'resources/views/partials/dial.blade.php' => [
        'language' => 'blade',
        'code' => <<<'CODE'
@php
  $colors = [
      'graphite' => ['#2C3134', '#E9E5DA', '#C8A25E'],
      'midnight' => ['#1B2B45', '#EDE7D9', '#C8A25E'],
      'cream' => ['#E8DCC3', '#2A2521', '#8C6B33'],
      'olive' => ['#4A4F3A', '#EFEAD9', '#C8A25E'],
      'black' => ['#17181A', '#F1EEE6', '#B99356'],
      'sand' => ['#D6C1A0', '#2A2521', '#7A5A2C'],
  ];
  $palette = $colors[$dial ?? 'graphite'] ?? $colors['graphite'];
  [$face, $marks, $ring] = $palette;
  $uid = 'd'.substr(md5(($dial ?? 'graphite').($label ?? '')), 0, 6);
@endphp
<svg class="dial" viewBox="0 0 120 120" role="img" aria-label="Циферблат {{ $label ?? 'часов' }}">
  <circle cx="60" cy="60" r="57" fill="{{ $face }}"/>
  <circle cx="60" cy="60" r="57" fill="none" stroke="{{ $ring }}" stroke-width="1.4"/>
  <circle cx="60" cy="60" r="50" fill="none" stroke="{{ $marks }}" stroke-opacity=".18" stroke-width="1"/>
  @for ($i = 0; $i < 12; $i++)
    <line x1="60" y1="12" x2="60" y2="{{ $i % 3 === 0 ? 19 : 16 }}"
          stroke="{{ $marks }}" stroke-width="{{ $i % 3 === 0 ? 2.2 : 1 }}"
          stroke-linecap="round" transform="rotate({{ $i * 30 }} 60 60)" opacity="{{ $i % 3 === 0 ? .9 : .55 }}"/>
  @endfor
  <text x="60" y="38" text-anchor="middle" fill="{{ $marks }}" font-size="5.5" letter-spacing="1.4" opacity=".75">{{ mb_strtoupper($brand ?? 'ТЧ') }}</text>
  <line x1="60" y1="60" x2="60" y2="34" stroke="{{ $marks }}" stroke-width="2.6" stroke-linecap="round" transform="rotate(305 60 60)"/>
  <line x1="60" y1="60" x2="60" y2="26" stroke="{{ $marks }}" stroke-width="1.7" stroke-linecap="round" transform="rotate(60 60 60)"/>
  <line x1="60" y1="60" x2="60" y2="22" stroke="{{ $ring }}" stroke-width="1" stroke-linecap="round" transform="rotate(200 60 60)"/>
  <circle cx="60" cy="60" r="2.6" fill="{{ $ring }}"/>
</svg>
CODE,
    ],
    'public/css/app.css' => [
        'language' => 'css',
        'code' => <<<'CODE'
/* Точный ход — часы, латунь, бумага.
   Один акцент (латунь), один тёмный блок (зелёный), два радиуса. */

:root {
  --bg: #efefea;
  --surface: #fbfaf6;
  --surface-2: #f4f2eb;
  --ink: #15181a;
  --ink-soft: #3b4245;
  --muted: #6c7377;
  --line: #d8d7cf;
  --line-strong: #b9b8ae;
  --green: #12352c;
  --green-2: #1b4a3b;
  --cream: #f4efe3;
  --brass: #a6772c;
  --brass-light: #e4d2ae;
  --danger: #a33b2e;
  --danger-bg: #f7e7e3;
  --success: #1f6b4a;
  --success-bg: #e4efe8;
  --radius: 2px;
  --radius-lg: 10px;
  --shadow: 0 22px 45px -32px rgba(18, 30, 26, 0.55);
  --font-display: "Cormorant Garamond", Georgia, "Times New Roman", serif;
  --font-body: "Golos Text", "Segoe UI", Arial, sans-serif;
  --wrap: 1200px;
}

*,
*::before,
*::after {
  box-sizing: border-box;
}

html {
  scroll-behavior: smooth;
}

body {
  margin: 0;
  background: var(--bg);
  color: var(--ink);
  font-family: var(--font-body);
  font-size: 16px;
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
}

h1,
h2,
h3 {
  margin: 0;
  font-family: var(--font-display);
  font-weight: 600;
  line-height: 1.02;
  letter-spacing: -0.01em;
}

p {
  margin: 0;
}

a {
  color: inherit;
  text-decoration-thickness: 1px;
  text-underline-offset: 3px;
}

img,
svg {
  display: block;
  max-width: 100%;
}

button,
input,
select,
textarea {
  font: inherit;
  color: inherit;
}

.wrap {
  width: 100%;
  max-width: var(--wrap);
  margin: 0 auto;
  padding: 0 32px;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

.skip-link {
  position: absolute;
  left: 16px;
  top: -60px;
  z-index: 100;
  padding: 10px 16px;
  background: var(--green);
  color: var(--cream);
  font-size: 14px;
}

.skip-link:focus {
  top: 10px;
}

:focus-visible {
  outline: 2px solid var(--brass);
  outline-offset: 2px;
}

/* Шапка ------------------------------------------------------------------ */

.site-header {
  position: sticky;
  top: 0;
  z-index: 40;
  background: rgba(251, 250, 246, 0.94);
  border-bottom: 1px solid var(--line);
  backdrop-filter: blur(8px);
}

.header-inner {
  display: flex;
  align-items: center;
  gap: 32px;
  min-height: 72px;
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
}

.brand-mark {
  display: grid;
  place-items: center;
  width: 38px;
  height: 38px;
  border: 1px solid var(--brass);
  border-radius: 50%;
  color: var(--brass);
}

.brand-mark svg {
  width: 22px;
  height: 22px;
}

.brand-text {
  display: flex;
  flex-direction: column;
  line-height: 1.1;
}

.brand-text strong {
  font-family: var(--font-display);
  font-size: 21px;
  font-weight: 600;
  letter-spacing: 0.01em;
}

.brand-text span {
  font-size: 11.5px;
  color: var(--muted);
  letter-spacing: 0.04em;
}

.site-nav {
  display: flex;
  align-items: center;
  gap: 26px;
  margin-left: 18px;
}

.site-nav a {
  padding: 6px 0;
  font-size: 15px;
  color: var(--ink-soft);
  text-decoration: none;
  border-bottom: 1px solid transparent;
}

.site-nav a:hover {
  color: var(--ink);
  border-bottom-color: var(--line-strong);
}

.site-nav a.is-active {
  color: var(--ink);
  border-bottom-color: var(--brass);
}

.header-user {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-left: auto;
  font-size: 14px;
}

.header-user form {
  margin: 0;
}

.who {
  color: var(--muted);
}

.link-btn {
  padding: 6px 0;
  border: 0;
  background: none;
  font-size: 14px;
  color: var(--ink-soft);
  text-decoration: underline;
  text-underline-offset: 3px;
  cursor: pointer;
}

.link-btn:hover {
  color: var(--ink);
}

/* Кнопки ----------------------------------------------------------------- */

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 46px;
  padding: 0 24px;
  border: 1px solid var(--green);
  border-radius: var(--radius);
  background: var(--green);
  color: var(--cream);
  font-size: 15px;
  font-weight: 500;
  text-decoration: none;
  cursor: pointer;
  transition: background-color 160ms ease, border-color 160ms ease, color 160ms ease;
}

.btn:hover {
  background: var(--green-2);
  border-color: var(--green-2);
}

.btn:active {
  transform: translateY(1px);
}

.btn-small {
  min-height: 38px;
  padding: 0 16px;
  font-size: 14px;
}

.btn-block {
  width: 100%;
}

.btn-light {
  background: var(--cream);
  border-color: var(--cream);
  color: var(--green);
}

.btn-light:hover {
  background: #fff;
  border-color: #fff;
}

.btn.is-disabled {
  background: transparent;
  border-color: var(--line-strong);
  color: var(--muted);
  cursor: not-allowed;
}

.link-arrow {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 15px;
  color: var(--ink);
  text-decoration: none;
  border-bottom: 1px solid var(--line-strong);
  padding-bottom: 2px;
}

.link-arrow::after {
  content: "→";
  color: var(--brass);
}

.link-arrow:hover {
  border-bottom-color: var(--brass);
}

/* Тёмная витрина --------------------------------------------------------- */

.hero {
  background: var(--green);
  color: var(--cream);
}

.hero-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
  gap: 72px;
  align-items: center;
  padding-top: 72px;
  padding-bottom: 80px;
}

.hero-kicker {
  margin-bottom: 22px;
  font-size: 14px;
  color: var(--brass-light);
}

.hero h1 {
  max-width: 15ch;
  font-size: clamp(42px, 5.2vw, 72px);
  color: var(--cream);
}

.lead {
  max-width: 46ch;
  margin-top: 22px;
  font-size: 17px;
  line-height: 1.62;
  color: rgba(244, 239, 227, 0.78);
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 24px;
  margin-top: 32px;
}

.hero .btn {
  background: var(--cream);
  border-color: var(--cream);
  color: var(--green);
}

.hero .btn:hover {
  background: #fff;
  border-color: #fff;
}

.hero .link-arrow {
  color: var(--cream);
  border-bottom-color: rgba(244, 239, 227, 0.35);
}

.hero .link-arrow:hover {
  border-bottom-color: var(--brass-light);
}

.hero-facts {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 20px;
  margin: 44px 0 0;
  padding-top: 22px;
  border-top: 1px solid rgba(244, 239, 227, 0.18);
}

.hero-facts dt {
  font-size: 12.5px;
  color: rgba(244, 239, 227, 0.6);
}

.hero-facts dd {
  margin: 4px 0 0;
  font-family: var(--font-display);
  font-size: 26px;
  color: var(--cream);
}

/* Часы в шапке — сигнатурный элемент ------------------------------------- */

.clock-card {
  justify-self: end;
  width: 100%;
  max-width: 380px;
  padding: 22px 24px 20px;
  background: var(--surface);
  color: var(--ink);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow);
}

.clock-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 12px;
  color: var(--muted);
}

.clock-live {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: var(--success);
}

.clock-live i {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: currentColor;
}

.clock {
  width: 100%;
  margin: 10px auto 4px;
}

.clock-digital {
  display: flex;
  align-items: baseline;
  justify-content: center;
  gap: 10px;
  font-family: var(--font-display);
  font-variant-numeric: tabular-nums;
  font-size: 46px;
  line-height: 1;
}

.clock-seconds {
  font-size: 20px;
  color: var(--brass);
}

.seconds-rail {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  height: 20px;
  margin: 14px 2px 12px;
  border-bottom: 1px solid var(--line);
}

.seconds-rail i {
  width: 1px;
  height: 6px;
  background: var(--line-strong);
  transition: height 200ms ease, background-color 200ms ease;
}

.seconds-rail i.is-now {
  height: 18px;
  background: var(--brass);
}

.clock-note {
  text-align: center;
  font-size: 12.5px;
  color: var(--muted);
}

/* Секции ----------------------------------------------------------------- */

.section-head {
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  justify-content: space-between;
  gap: 12px 32px;
  margin-bottom: 26px;
}

.section-head h2 {
  font-size: clamp(28px, 3vw, 40px);
}

.section-head p {
  max-width: 46ch;
  font-size: 15px;
  color: var(--muted);
}

.catalog {
  padding-top: 64px;
  padding-bottom: 24px;
}

.notice {
  margin-bottom: 18px;
  padding: 14px 18px;
  border: 1px solid var(--line);
  border-left: 3px solid var(--brass);
  background: var(--surface);
  font-size: 14.5px;
}

.notice a {
  color: var(--green);
  font-weight: 500;
}

.watch-list {
  border-top: 1px solid var(--line-strong);
}

.watch-row {
  display: grid;
  grid-template-columns: 84px minmax(0, 1fr) auto auto;
  gap: 16px 28px;
  align-items: center;
  padding: 20px 4px;
  border-bottom: 1px solid var(--line);
  transition: background-color 160ms ease;
}

.watch-row:hover {
  background: var(--surface);
}

.dial {
  width: 84px;
  height: 84px;
  flex: none;
}

.watch-main h3 {
  font-size: 24px;
}

.watch-main h3 span {
  color: var(--muted);
  font-weight: 500;
}

.watch-main p {
  margin-top: 6px;
  font-size: 14px;
  color: var(--ink-soft);
  max-width: 52ch;
}

.watch-rating {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px !important;
  color: var(--muted) !important;
}

.stars {
  color: var(--brass);
  letter-spacing: 1px;
}

.stars-empty {
  color: var(--line-strong);
}

.watch-specs {
  display: grid;
  grid-template-columns: repeat(3, auto);
  gap: 20px;
  margin: 0;
}

.watch-specs dt {
  font-size: 11.5px;
  color: var(--muted);
}

.watch-specs dd {
  margin: 2px 0 0;
  font-size: 14px;
  white-space: nowrap;
}

.watch-buy {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 8px;
  min-width: 132px;
}

.price {
  font-family: var(--font-display);
  font-size: 26px;
  font-weight: 600;
  font-variant-numeric: tabular-nums;
  white-space: nowrap;
}

/* Проверка перед выдачей ------------------------------------------------- */

.service {
  margin-top: 56px;
  padding: 56px 0;
  background: var(--surface-2);
  border-top: 1px solid var(--line);
  border-bottom: 1px solid var(--line);
}

.service-list {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 0;
  margin: 0;
  padding: 0;
  list-style: none;
}

.service-list li {
  display: flex;
  gap: 14px;
  padding: 4px 24px;
  border-left: 1px solid var(--line);
}

.service-list li:first-child {
  padding-left: 0;
  border-left: 0;
}

.service-list b {
  font-family: var(--font-display);
  font-size: 22px;
  color: var(--brass);
  font-weight: 600;
}

.service-list h3 {
  font-family: var(--font-body);
  font-size: 16px;
  font-weight: 600;
  line-height: 1.3;
}

.service-list p {
  margin-top: 6px;
  font-size: 14px;
  color: var(--muted);
}

/* Отзывы ----------------------------------------------------------------- */

.reviews {
  padding-top: 56px;
  padding-bottom: 8px;
}

.review-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 32px;
}

.review {
  margin: 0;
  padding-top: 18px;
  border-top: 2px solid var(--ink);
}

.review-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  font-size: 13px;
  color: var(--muted);
}

.review blockquote {
  margin: 14px 0 16px;
  font-family: var(--font-display);
  font-size: 20px;
  line-height: 1.35;
}

.review figcaption {
  display: flex;
  flex-direction: column;
  font-size: 14px;
  font-weight: 500;
}

.review figcaption span {
  font-weight: 400;
  font-size: 13px;
  color: var(--muted);
}

/* Призыв ----------------------------------------------------------------- */

.cta {
  margin-top: 64px;
  margin-bottom: 64px;
}

.cta-inner {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  padding: 40px 44px;
  background: var(--green);
  color: var(--cream);
  border-radius: var(--radius-lg);
}

.cta h2 {
  font-size: clamp(26px, 2.6vw, 36px);
}

.cta p {
  margin-top: 10px;
  max-width: 48ch;
  font-size: 15px;
  color: rgba(244, 239, 227, 0.75);
}

/* Формы ------------------------------------------------------------------ */

.form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.field-row {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 18px;
}

.field label,
.pay legend,
.rating legend {
  font-size: 13.5px;
  font-weight: 500;
}

.optional {
  font-weight: 400;
  color: var(--muted);
}

.field input,
.field select,
.field textarea,
.search input,
.status-form select {
  width: 100%;
  min-height: 46px;
  padding: 10px 12px;
  border: 1px solid var(--line-strong);
  border-radius: var(--radius);
  background: #fff;
  font-size: 15px;
  transition: border-color 150ms ease, box-shadow 150ms ease;
}

.field textarea {
  min-height: 96px;
  resize: vertical;
}

.field input:focus,
.field select:focus,
.field textarea:focus,
.search input:focus,
.status-form select:focus {
  border-color: var(--green);
  box-shadow: 0 0 0 3px rgba(18, 53, 44, 0.12);
  outline: none;
}

.field input[aria-invalid="true"],
.field select[aria-invalid="true"],
.field textarea[aria-invalid="true"] {
  border-color: var(--danger);
}

.hint {
  font-size: 12.5px;
  color: var(--muted);
}

.check {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 14px;
  color: var(--ink-soft);
}

.check input {
  width: 18px;
  height: 18px;
  accent-color: var(--green);
}

.pay {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin: 0;
  padding: 0;
  border: 0;
}

.pay legend {
  margin-bottom: 8px;
  padding: 0;
}

.pay-option {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 14px 16px;
  border: 1px solid var(--line-strong);
  border-radius: var(--radius);
  background: #fff;
  cursor: pointer;
}

.pay-option:has(input:checked) {
  border-color: var(--green);
  box-shadow: inset 0 0 0 1px var(--green);
}

.pay-option input {
  width: 18px;
  height: 18px;
  margin-top: 3px;
  accent-color: var(--green);
}

.pay-option b {
  display: block;
  font-size: 15px;
  font-weight: 500;
}

.pay-option small {
  display: block;
  margin-top: 2px;
  font-size: 13px;
  color: var(--muted);
}

/* Страницы --------------------------------------------------------------- */

.page {
  padding-top: 48px;
  padding-bottom: 72px;
}

.page-head {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-end;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 28px;
  padding-bottom: 22px;
  border-bottom: 1px solid var(--line-strong);
}

.page-head h1 {
  font-size: clamp(32px, 3.6vw, 48px);
}

.page-head p {
  max-width: 56ch;
  margin-top: 10px;
  font-size: 15px;
  color: var(--muted);
}

.alert {
  margin-bottom: 20px;
  padding: 14px 18px;
  border: 1px solid var(--line);
  border-left: 3px solid var(--muted);
  background: var(--surface);
  font-size: 14.5px;
}

.alert p + p {
  margin-top: 4px;
}

.alert--success {
  border-left-color: var(--success);
  background: var(--success-bg);
  color: #14442f;
}

.alert--error {
  border-left-color: var(--danger);
  background: var(--danger-bg);
  color: #6d2519;
}

.empty {
  padding: 40px 0;
  border-top: 1px solid var(--line);
}

.empty h2 {
  font-size: 26px;
}

.empty p {
  margin: 8px 0 20px;
  color: var(--muted);
}

/* Заказы ----------------------------------------------------------------- */

.order-list {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.order {
  border: 1px solid var(--line);
  border-radius: var(--radius);
  background: var(--surface);
}

.order-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 14px 20px;
  border-bottom: 1px solid var(--line);
}

.order-id {
  display: flex;
  align-items: baseline;
  gap: 14px;
  font-size: 14px;
  font-weight: 500;
}

.order-id time {
  font-size: 12.5px;
  font-weight: 400;
  color: var(--muted);
}

.status {
  display: inline-flex;
  align-items: center;
  min-height: 26px;
  padding: 0 10px;
  border-radius: 999px;
  font-size: 12.5px;
  white-space: nowrap;
}

.status.is-new {
  background: #f6ecd9;
  color: #7a5312;
}

.status.is-ready {
  background: var(--success-bg);
  color: #14503a;
}

.status.is-issued {
  background: #e8e8e3;
  color: var(--ink-soft);
}

.order-body {
  display: grid;
  grid-template-columns: 72px minmax(0, 1fr) auto;
  gap: 20px;
  align-items: center;
  padding: 18px 20px;
}

.order-body .dial {
  width: 72px;
  height: 72px;
}

.order-model h2 {
  font-family: var(--font-body);
  font-size: 17px;
  font-weight: 600;
  line-height: 1.3;
}

.order-model p {
  margin-top: 4px;
  font-size: 13.5px;
  color: var(--muted);
}

.order-meta {
  display: flex;
  gap: 28px;
  margin: 0;
}

.order-meta dt {
  font-size: 11.5px;
  color: var(--muted);
}

.order-meta dd {
  margin: 2px 0 0;
  font-size: 14px;
  white-space: nowrap;
}

.order-meta .price {
  font-size: 20px;
}

.order-comment {
  padding: 0 20px 16px;
  font-size: 13.5px;
  color: var(--muted);
}

.order-note {
  padding: 0 20px 18px;
  font-size: 13.5px;
  color: var(--muted);
}

.order-review {
  padding: 16px 20px 20px;
  border-top: 1px dashed var(--line);
}

.order-review p {
  margin-top: 8px;
  font-size: 14.5px;
}

.order-review-label {
  font-size: 12.5px;
  color: var(--muted);
}

.review-form {
  border-top: 1px dashed var(--line);
}

.review-form summary {
  padding: 14px 20px;
  font-size: 14px;
  color: var(--green);
  cursor: pointer;
}

.review-form summary:hover {
  text-decoration: underline;
}

.review-form form {
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding: 0 20px 20px;
}

.rating {
  margin: 0;
  padding: 0;
  border: 0;
}

.rating-row {
  display: flex;
  gap: 8px;
  margin-top: 8px;
}

.rating-row label {
  cursor: pointer;
}

.rating-row input {
  position: absolute;
  opacity: 0;
  width: 1px;
  height: 1px;
}

.rating-row span {
  display: grid;
  place-items: center;
  width: 40px;
  height: 40px;
  border: 1px solid var(--line-strong);
  border-radius: var(--radius);
  background: #fff;
  font-size: 15px;
}

.rating-row input:checked + span {
  background: var(--green);
  border-color: var(--green);
  color: var(--cream);
}

.rating-row input:focus-visible + span {
  outline: 2px solid var(--brass);
  outline-offset: 2px;
}

/* Оформление заказа ------------------------------------------------------ */

.order-layout {
  display: grid;
  grid-template-columns: minmax(0, 1.35fr) minmax(0, 0.65fr);
  gap: 40px;
  align-items: start;
}

.card-form {
  padding: 26px 28px;
  border: 1px solid var(--line);
  border-radius: var(--radius);
  background: var(--surface);
}

.order-summary {
  position: sticky;
  top: 96px;
  padding: 24px;
  border: 1px solid var(--line);
  border-radius: var(--radius);
  background: var(--surface-2);
}

.order-summary h2 {
  font-size: 24px;
  margin-bottom: 18px;
}

.summary-model {
  display: flex;
  align-items: center;
  gap: 14px;
  padding-bottom: 18px;
  border-bottom: 1px solid var(--line);
}

.summary-dial .dial {
  width: 64px;
  height: 64px;
}

.summary-text {
  display: flex;
  flex-direction: column;
  gap: 4px;
  min-width: 0;
}

.summary-text b {
  font-size: 15px;
}

.summary-text span {
  font-size: 13.5px;
  color: var(--muted);
}

.summary-rows {
  margin: 16px 0 0;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.summary-rows div {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  font-size: 14px;
}

.summary-rows dt {
  color: var(--muted);
}

.summary-rows dd {
  margin: 0;
  font-variant-numeric: tabular-nums;
}

.summary-total {
  margin-top: 6px;
  padding-top: 12px;
  border-top: 1px solid var(--line);
}

.summary-total dd {
  font-family: var(--font-display);
  font-size: 24px;
  font-weight: 600;
}

.summary-note {
  margin-top: 16px;
  font-size: 12.5px;
  color: var(--muted);
}

/* Админка ---------------------------------------------------------------- */

.admin-bar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 18px;
}

.tabs {
  display: flex;
  flex-wrap: wrap;
  gap: 4px 22px;
}

.tabs a {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 0;
  font-size: 14px;
  color: var(--ink-soft);
  text-decoration: none;
  border-bottom: 2px solid transparent;
}

.tabs a b {
  font-weight: 500;
  color: var(--muted);
  font-variant-numeric: tabular-nums;
}

.tabs a:hover {
  color: var(--ink);
}

.tabs a.is-active {
  color: var(--ink);
  border-bottom-color: var(--brass);
}

.search {
  display: flex;
  gap: 8px;
}

.search input {
  width: 220px;
  min-height: 38px;
  padding: 6px 12px;
  font-size: 14px;
}

.table-wrap {
  overflow-x: auto;
  border-top: 1px solid var(--line-strong);
}

.admin-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.admin-table th {
  padding: 10px 12px;
  text-align: left;
  font-size: 11.5px;
  font-weight: 500;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: var(--muted);
  border-bottom: 1px solid var(--line);
  white-space: nowrap;
}

.admin-table td {
  padding: 14px 12px;
  border-bottom: 1px solid var(--line);
  vertical-align: top;
}

.admin-table .num {
  text-align: right;
}

.admin-table .price {
  font-size: 17px;
}

.cell-sub {
  display: block;
  margin-top: 2px;
  font-size: 12.5px;
  color: var(--muted);
}

.cell-status {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.status-form {
  display: flex;
  gap: 6px;
  align-items: center;
}

.status-form select {
  width: auto;
  min-height: 38px;
  padding: 4px 8px;
  font-size: 13.5px;
}

/* Пагинация -------------------------------------------------------------- */

.pager {
  margin-top: 26px;
}

.pagination {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin: 0;
  padding: 0;
  list-style: none;
}

.pagination li {
  display: inline-flex;
}

.pagination a,
.pagination span {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 38px;
  height: 38px;
  padding: 0 10px;
  border: 1px solid var(--line-strong);
  border-radius: var(--radius);
  font-size: 14px;
  text-decoration: none;
  background: #fff;
}

.pagination a:hover {
  border-color: var(--green);
}

.pagination .is-active span {
  background: var(--green);
  border-color: var(--green);
  color: var(--cream);
}

.pagination .is-disabled span {
  color: var(--line-strong);
}

/* Вход и регистрация ----------------------------------------------------- */

.auth {
  display: grid;
  grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr);
  gap: 64px;
  padding-top: 56px;
  padding-bottom: 80px;
}

.auth-panel {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 32px;
  padding: 36px;
  background: var(--surface-2);
  border: 1px solid var(--line);
  border-radius: var(--radius-lg);
}

.auth-panel--dark {
  background: var(--green);
  color: var(--cream);
  border-color: var(--green);
}

.auth-quote {
  font-family: var(--font-display);
  font-size: 28px;
  line-height: 1.25;
}

.auth-quote-by {
  margin-top: 14px;
  font-size: 13.5px;
  color: var(--muted);
}

.auth-panel--dark .auth-quote-by {
  color: rgba(244, 239, 227, 0.65);
}

.auth-panel-clock {
  max-width: 220px;
}

.auth-panel-clock .dial {
  width: 100%;
  height: auto;
}

.auth-list {
  margin: 0;
  padding: 0;
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 10px;
  font-size: 14.5px;
}

.auth-list li {
  padding-top: 10px;
  border-top: 1px solid rgba(244, 239, 227, 0.18);
}

.auth-form {
  max-width: 520px;
  padding-top: 8px;
}

.auth-form h1 {
  font-size: clamp(30px, 3vw, 40px);
}

.auth-sub {
  margin: 10px 0 26px;
  font-size: 15px;
  color: var(--muted);
}

.auth-alt {
  margin-top: 20px;
  font-size: 14px;
  color: var(--muted);
}

.auth-alt a {
  color: var(--green);
  font-weight: 500;
}

/* Подвал ----------------------------------------------------------------- */

.site-footer {
  padding: 40px 0 48px;
  background: var(--ink);
  color: rgba(244, 239, 227, 0.72);
}

.footer-inner {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  gap: 28px;
}

.footer-brand strong {
  display: block;
  font-family: var(--font-display);
  font-size: 22px;
  color: var(--cream);
  margin-bottom: 8px;
}

.footer-brand p,
.footer-note p {
  font-size: 13px;
  line-height: 1.6;
}

.footer-note {
  max-width: 44ch;
  text-align: right;
}

.footer-note p + p {
  margin-top: 6px;
  color: rgba(244, 239, 227, 0.45);
}

/* Адаптив ---------------------------------------------------------------- */

@media (max-width: 1080px) {
  .hero-grid {
    gap: 40px;
    padding-top: 56px;
    padding-bottom: 64px;
  }

  .watch-row {
    grid-template-columns: 76px minmax(0, 1fr) auto;
  }

  .watch-specs {
    grid-column: 2;
    grid-row: 2;
    gap: 24px;
  }

  .watch-buy {
    grid-column: 3;
    grid-row: 1 / span 2;
  }

  .service-list {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 28px 0;
  }

  .service-list li:nth-child(3) {
    padding-left: 0;
    border-left: 0;
  }
}

@media (max-width: 900px) {
  .header-inner {
    flex-wrap: wrap;
    gap: 10px 24px;
    padding-top: 12px;
    padding-bottom: 12px;
  }

  .site-nav {
    order: 3;
    width: 100%;
    margin-left: 0;
    gap: 20px;
  }

  .site-nav a {
    min-height: 40px;
    display: inline-flex;
    align-items: center;
  }

  .header-user {
    margin-left: auto;
  }

  .hero-grid,
  .auth {
    grid-template-columns: 1fr;
  }

  .clock-card {
    justify-self: start;
    max-width: 360px;
  }

  .auth-panel {
    order: 2;
  }

  .auth-form {
    max-width: 100%;
  }

  .review-grid {
    grid-template-columns: 1fr 1fr;
  }

  .review-grid .review:last-child {
    display: none;
  }

  .order-layout {
    grid-template-columns: 1fr;
  }

  .order-summary {
    position: static;
  }
}

@media (max-width: 720px) {
  .wrap {
    padding: 0 18px;
  }

  .hero h1 {
    font-size: 40px;
  }

  .hero-facts dd {
    font-size: 22px;
  }

  .watch-row {
    grid-template-columns: 64px minmax(0, 1fr);
    gap: 12px 16px;
    padding: 18px 0;
  }

  .watch-row .dial {
    width: 64px;
    height: 64px;
  }

  .watch-main h3 {
    font-size: 21px;
  }

  .watch-specs {
    grid-column: 1 / -1;
    grid-row: auto;
    padding-top: 10px;
    border-top: 1px dashed var(--line);
  }

  .watch-buy {
    grid-column: 1 / -1;
    grid-row: auto;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    min-width: 0;
    padding-top: 4px;
  }

  .service {
    padding: 40px 0;
  }

  .service-list {
    grid-template-columns: 1fr;
    gap: 22px;
  }

  .service-list li {
    padding: 0;
    border-left: 0;
  }

  .review-grid {
    grid-template-columns: 1fr;
  }

  .review-grid .review:last-child {
    display: block;
  }

  .cta-inner {
    padding: 28px 22px;
  }

  .page-head {
    align-items: flex-start;
  }

  .order-body {
    grid-template-columns: 56px minmax(0, 1fr);
    gap: 14px;
  }

  .order-body .dial {
    width: 56px;
    height: 56px;
  }

  .order-meta {
    grid-column: 1 / -1;
    gap: 20px;
    padding-top: 12px;
    border-top: 1px dashed var(--line);
  }

  .card-form {
    padding: 20px 18px;
  }

  .field-row {
    grid-template-columns: 1fr;
  }

  .admin-bar {
    align-items: stretch;
  }

  .search input {
    width: 100%;
  }

  .search {
    width: 100%;
  }

  .table-wrap {
    border-top: 0;
  }

  .admin-table thead {
    display: none;
  }

  .admin-table,
  .admin-table tbody,
  .admin-table tr,
  .admin-table td {
    display: block;
    width: 100%;
  }

  .admin-table tr {
    padding: 14px 0;
    border-bottom: 1px solid var(--line);
  }

  .admin-table td {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    padding: 5px 0;
    border: 0;
    text-align: right;
  }

  .admin-table td::before {
    content: attr(data-label);
    flex: none;
    text-align: left;
    font-size: 12.5px;
    color: var(--muted);
  }

  .admin-table .num {
    text-align: right;
  }

  .cell-sub {
    text-align: right;
  }

  .cell-status {
    align-items: flex-end;
  }

  .footer-note {
    text-align: left;
  }
}

@media (max-width: 480px) {
  .hero-actions {
    align-items: stretch;
    flex-direction: column;
  }

  .hero-actions .btn,
  .hero-actions .link-arrow {
    justify-content: center;
  }

  .clock-digital {
    font-size: 38px;
  }

  .order-meta {
    flex-wrap: wrap;
    gap: 14px 22px;
  }

  .tabs {
    gap: 2px 16px;
  }
}

@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
    scroll-behavior: auto !important;
  }
}
CODE,
    ],
    'public/js/app.js' => [
        'language' => 'js',
        'code' => <<<'CODE'
// Часы в шапке и предварительный расчёт заказа.
// Сумма здесь только для удобства: окончательную считает сервер.

(function () {
  'use strict';

  var pad = function (value) {
    return String(value).padStart(2, '0');
  };

  var timeEl = document.getElementById('clock-time');

  if (timeEl) {
    var secondsEl = document.getElementById('clock-seconds');
    var secondHand = document.getElementById('clock-second-line');
    var hourHand = document.getElementById('clock-hand-hour');
    var minuteHand = document.getElementById('clock-hand-minute');
    var railTicks = document.querySelectorAll('#seconds-rail i');

    var tick = function () {
      var now = new Date();
      var hours = now.getHours();
      var minutes = now.getMinutes();
      var seconds = now.getSeconds();

      timeEl.textContent = pad(hours) + ':' + pad(minutes);

      if (secondsEl) {
        secondsEl.textContent = pad(seconds);
      }

      if (secondHand) {
        secondHand.setAttribute('transform', 'rotate(' + seconds * 6 + ' 100 100)');
      }

      if (hourHand) {
        hourHand.setAttribute('transform', 'rotate(' + ((hours % 12) * 30 + minutes * 0.5) + ' 100 100)');
      }

      if (minuteHand) {
        minuteHand.setAttribute('transform', 'rotate(' + (minutes * 6 + seconds * 0.1) + ' 100 100)');
      }

      railTicks.forEach(function (element, index) {
        element.classList.toggle('is-now', index === seconds);
      });
    };

    tick();
    window.setInterval(tick, 1000);
  }

  var select = document.getElementById('watch_id');

  if (!select) {
    return;
  }

  var quantity = document.getElementById('quantity');
  var totalEl = document.getElementById('summary-total');
  var nameEl = document.getElementById('summary-name');
  var priceEl = document.getElementById('summary-price');
  var qtyEl = document.getElementById('summary-qty');
  var payEl = document.getElementById('summary-pay');
  var money = new Intl.NumberFormat('ru-RU');

  var sync = function () {
    var option = select.options[select.selectedIndex];

    if (!option) {
      return;
    }

    var price = Number(option.dataset.price || 0);
    var count = Math.max(1, Math.min(3, Number(quantity.value || 1)));
    var total = price * count;

    nameEl.textContent = option.dataset.model || option.textContent;
    priceEl.textContent = money.format(price) + ' ₽ за пару';
    qtyEl.textContent = count + ' шт.';
    totalEl.textContent = money.format(total) + ' ₽';
  };

  var syncPayment = function () {
    var checked = document.querySelector('input[name="payment"]:checked');

    if (checked && payEl) {
      payEl.textContent = checked.value === 'sbp' ? 'Переводом по СБП' : 'При получении';
    }
  };

  select.addEventListener('change', sync);
  quantity.addEventListener('input', sync);

  document.querySelectorAll('input[name="payment"]').forEach(function (input) {
    input.addEventListener('change', syncPayment);
  });

  sync();
  syncPayment();
})();
CODE,
    ],
    'app/Http/Controllers/OrderController.php' => [
        'language' => 'php',
        'code' => <<<'CODE'
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Watch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with(['watch', 'review'])
            ->latest()
            ->paginate(8);

        return view('orders.index', [
            'orders' => $orders,
        ]);
    }

    public function create(Request $request)
    {
        return view('orders.create', [
            'watches' => Watch::orderBy('price')->get(),
            'selected' => $request->integer('watch'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'watch_id' => ['required', 'integer', 'exists:watches,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:3'],
            'pickup_date' => ['required', 'date', 'after_or_equal:today'],
            'payment' => ['required', 'in:onsite,sbp'],
            'comment' => ['nullable', 'string', 'max:500'],
        ], [
            'watch_id.required' => 'Выберите модель часов.',
            'watch_id.exists' => 'Такой модели нет в каталоге.',
            'quantity.min' => 'Минимум одна пара часов.',
            'quantity.max' => 'За один заказ — не больше трёх пар.',
            'pickup_date.after_or_equal' => 'Дата получения не может быть в прошлом.',
            'payment.in' => 'Выберите способ оплаты из двух вариантов.',
            'comment.max' => 'Комментарий длиннее 500 символов.',
        ]);

        $order = DB::transaction(function () use ($data, $request) {
            $watch = Watch::lockForUpdate()->findOrFail($data['watch_id']);

            if ($watch->stock < $data['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => 'В наличии осталось '.$watch->stock.' шт. Уменьшите количество.',
                ]);
            }

            $watch->decrement('stock', $data['quantity']);

            // Сумму считает сервер: цену из формы подменить нельзя.
            return $request->user()->orders()->create([
                'watch_id' => $watch->id,
                'quantity' => $data['quantity'],
                'total' => $watch->price * $data['quantity'],
                'pickup_date' => $data['pickup_date'],
                'payment' => $data['payment'],
                'status' => 'new',
                'comment' => $data['comment'] ?? null,
            ]);
        });

        return redirect()->route('orders.index')
            ->with('success', 'Заказ №'.$order->id.' принят. Статус — «Новый».');
    }
}
CODE,
    ],
    'app/Http/Controllers/CatalogController.php' => [
        'language' => 'php',
        'code' => <<<'CODE'
<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Watch;

class CatalogController extends Controller
{
    public function index()
    {
        $watches = Watch::query()
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderBy('price')
            ->get();

        $reviews = Review::query()
            ->with(['user:id,fio', 'order.watch:id,brand,model'])
            ->latest()
            ->limit(3)
            ->get();

        return view('catalog', [
            'watches' => $watches,
            'reviews' => $reviews,
        ]);
    }
}
CODE,
    ],
    'resources/views/orders/create.blade.php' => [
        'language' => 'blade',
        'code' => <<<'CODE'
@extends('layouts.app')

@section('title', 'Оформление заказа — Точный ход')

@section('content')
  <section class="page wrap">
    <header class="page-head">
      <div>
        <h1>Оформление заказа</h1>
        <p>Выберите модель, дату получения и способ оплаты. Сумму посчитаем на сервере.</p>
      </div>
      <a class="link-arrow" href="{{ route('catalog') }}">Вернуться в каталог</a>
    </header>

    @if ($errors->any())
      <div class="alert alert--error" role="alert">
        @foreach ($errors->all() as $error)
          <p>{{ $error }}</p>
        @endforeach
      </div>
    @endif

    <div class="order-layout">
      <form method="POST" action="{{ route('orders.store') }}" class="form card-form" id="order-form">
        @csrf

        <div class="field">
          <label for="watch_id">Модель</label>
          <select id="watch_id" name="watch_id" required
                  @error('watch_id') aria-invalid="true" @enderror>
            @foreach ($watches as $watch)
              <option value="{{ $watch->id }}"
                      data-price="{{ $watch->price }}"
                      data-stock="{{ $watch->stock }}"
                      data-model="{{ $watch->brand }} {{ $watch->model }}"
                      @selected((int) old('watch_id', $selected) === $watch->id)
                      @disabled(! $watch->isAvailable())>
                {{ $watch->brand }} {{ $watch->model }} — {{ $watch->priceLabel() }}{{ $watch->isAvailable() ? '' : ' (нет в наличии)' }}
              </option>
            @endforeach
          </select>
          <span class="hint">В наличии шесть моделей. Цена указана за одну пару.</span>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="quantity">Количество, шт.</label>
            <input type="number" id="quantity" name="quantity" min="1" max="3" value="{{ old('quantity', 1) }}" required
                   @error('quantity') aria-invalid="true" @enderror>
            <span class="hint">Не больше трёх пар за один заказ.</span>
          </div>

          <div class="field">
            <label for="pickup_date">Дата получения</label>
            <input type="date" id="pickup_date" name="pickup_date" required
                   min="{{ now()->toDateString() }}"
                   value="{{ old('pickup_date', now()->addDays(2)->toDateString()) }}"
                   @error('pickup_date') aria-invalid="true" @enderror>
            <span class="hint">Раньше сегодняшнего дня выбрать нельзя.</span>
          </div>
        </div>

        <fieldset class="pay">
          <legend>Способ оплаты</legend>
          <label class="pay-option">
            <input type="radio" name="payment" value="onsite" @checked(old('payment', 'onsite') === 'onsite') required>
            <span>
              <b>При получении</b>
              <small>Наличными или картой в салоне, после примерки</small>
            </span>
          </label>
          <label class="pay-option">
            <input type="radio" name="payment" value="sbp" @checked(old('payment') === 'sbp')>
            <span>
              <b>Переводом по СБП</b>
              <small>Ссылку пришлём в день сборки заказа</small>
            </span>
          </label>
        </fieldset>

        <div class="field">
          <label for="comment">Комментарий <span class="optional">необязательно</span></label>
          <textarea id="comment" name="comment" rows="3" maxlength="500"
                    placeholder="Например: подогнать браслет под запястье 17 см">{{ old('comment') }}</textarea>
        </div>

        <button type="submit" class="btn btn-block">Оформить заказ</button>
      </form>

      <aside class="order-summary" aria-label="Итог заказа">
        <h2>Ваш заказ</h2>
        <div class="summary-model">
          <div class="summary-dial" id="summary-dial">
            @include('partials.dial', ['dial' => 'graphite', 'brand' => 'ТЧ', 'label' => 'Выбранная модель'])
          </div>
          <div class="summary-text">
            <b id="summary-name">Выберите модель</b>
            <span id="summary-price">—</span>
          </div>
        </div>

        <dl class="summary-rows">
          <div><dt>Количество</dt><dd id="summary-qty">1 шт.</dd></div>
          <div><dt>Оплата</dt><dd id="summary-pay">При получении</dd></div>
          <div class="summary-total"><dt>Итого</dt><dd id="summary-total">—</dd></div>
        </dl>

        <p class="summary-note">Цену и сумму проверяет сервер при сохранении. Если модель распродана, заказ не создастся.</p>
      </aside>
    </div>
  </section>
@endsection
CODE,
    ],
    'resources/views/orders/index.blade.php' => [
        'language' => 'blade',
        'code' => <<<'CODE'
@extends('layouts.app')

@section('title', 'Мои заказы — Точный ход')

@section('content')
  <section class="page wrap">
    <header class="page-head">
      <div>
        <h1>Мои заказы</h1>
        <p>Здесь видно, на каком этапе ваши часы: принят, готов к выдаче или уже у вас.</p>
      </div>
      <a class="btn" href="{{ route('orders.create') }}">Новый заказ</a>
    </header>

    @if ($errors->any())
      <div class="alert alert--error" role="alert">
        @foreach ($errors->all() as $error)
          <p>{{ $error }}</p>
        @endforeach
      </div>
    @endif

    @if ($orders->isEmpty())
      <div class="empty">
        <h2>Пока ни одного заказа</h2>
        <p>Выберите модель в каталоге — заказ появится здесь сразу после оформления.</p>
        <a class="btn" href="{{ route('catalog') }}">Перейти в каталог</a>
      </div>
    @else
      <div class="order-list">
        @foreach ($orders as $order)
          <article class="order">
            <header class="order-head">
              <div class="order-id">
                <span>Заказ №{{ $order->id }}</span>
                <time datetime="{{ $order->created_at->toDateString() }}">{{ $order->created_at->translatedFormat('j F Y') }}</time>
              </div>
              @include('partials.status', ['status' => $order->status, 'label' => $order->statusLabel()])
            </header>

            <div class="order-body">
              @include('partials.dial', ['dial' => $order->watch->dial, 'brand' => 'ТЧ', 'label' => $order->watch->model])

              <div class="order-model">
                <h2>{{ $order->watch->brand }} {{ $order->watch->model }}</h2>
                <p>{{ $order->quantity }} шт. · {{ $order->watch->case_mm }} мм · {{ $order->watch->movementLabel() }}</p>
              </div>

              <dl class="order-meta">
                <div><dt>Получение</dt><dd>{{ $order->pickup_date->translatedFormat('j F') }}</dd></div>
                <div><dt>Оплата</dt><dd>{{ $order->paymentLabel() }}</dd></div>
                <div><dt>Сумма</dt><dd class="price">{{ $order->totalLabel() }}</dd></div>
              </dl>
            </div>

            @if ($order->comment)
              <p class="order-comment">Комментарий: {{ $order->comment }}</p>
            @endif

            @if ($order->review)
              <div class="order-review">
                <div class="review-head">
                  <span class="stars" aria-label="Ваша оценка {{ $order->review->rating }} из 5">{{ str_repeat('★', $order->review->rating) }}<span class="stars-empty">{{ str_repeat('☆', 5 - $order->review->rating) }}</span></span>
                  <span class="order-review-label">Ваш отзыв</span>
                </div>
                <p>{{ $order->review->text }}</p>
              </div>
            @elseif ($order->canBeReviewed())
              <details class="review-form">
                <summary>Оставить отзыв о часах</summary>
                <form method="POST" action="{{ route('reviews.store', $order) }}">
                  @csrf
                  <fieldset class="rating">
                    <legend>Оценка</legend>
                    <div class="rating-row">
                      @for ($i = 1; $i <= 5; $i++)
                        <label>
                          <input type="radio" name="rating" value="{{ $i }}" @checked($i === 5) required>
                          <span>{{ $i }}</span>
                        </label>
                      @endfor
                    </div>
                  </fieldset>

                  <div class="field">
                    <label for="text-{{ $order->id }}">Что понравилось или не понравилось</label>
                    <textarea id="text-{{ $order->id }}" name="text" rows="3" required minlength="10" maxlength="600"
                              placeholder="Точность хода, посадка на руке, обслуживание в салоне"></textarea>
                    <span class="hint">От 10 до 600 символов. Отзыв появится в каталоге.</span>
                  </div>

                  <button type="submit" class="btn btn-small">Опубликовать отзыв</button>
                </form>
              </details>
            @elseif (! $order->isIssued())
              <p class="order-note">Отзыв можно будет оставить после выдачи заказа.</p>
            @endif
          </article>
        @endforeach
      </div>

      <div class="pager">
        {{ $orders->links() }}
      </div>
    @endif
  </section>
@endsection
CODE,
    ],
    'resources/views/partials/status.blade.php' => [
        'language' => 'blade',
        'code' => <<<'CODE'
@php
  $map = [
      'new' => 'is-new',
      'ready' => 'is-ready',
      'issued' => 'is-issued',
  ];
@endphp
<span class="status {{ $map[$status] ?? '' }}">{{ $label }}</span>
CODE,
    ],
    'app/Http/Controllers/Admin/OrderController.php' => [
        'language' => 'php',
        'code' => <<<'CODE'
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('search'));

        $orders = Order::query()
            ->with(['user:id,fio,login,phone', 'watch:id,brand,model,price'])
            ->when(array_key_exists($status, Order::STATUSES), fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('user', fn ($q) => $q->where('login', 'like', "%{$search}%"))
                        ->orWhereHas('watch', fn ($q) => $q->where('model', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'status' => $status,
            'search' => $search,
            'counts' => [
                'all' => Order::count(),
                'new' => Order::where('status', 'new')->count(),
                'ready' => Order::where('status', 'ready')->count(),
                'issued' => Order::where('status', 'issued')->count(),
            ],
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,ready,issued'],
        ], [
            'status.in' => 'Неизвестный статус заказа.',
        ]);

        if (! $order->canMoveTo($data['status'])) {
            throw ValidationException::withMessages([
                'status' => 'Из статуса «'.$order->statusLabel().'» нельзя перейти в «'.Order::STATUSES[$data['status']].'».',
            ]);
        }

        $order->update(['status' => $data['status']]);

        return back()->with('success', 'Заказ №'.$order->id.' — «'.$order->statusLabel().'».');
    }
}
CODE,
    ],
    'resources/views/admin/orders/index.blade.php' => [
        'language' => 'blade',
        'code' => <<<'CODE'
@extends('layouts.app')

@section('title', 'Заказы салона — Точный ход')

@section('content')
  <section class="page wrap">
    <header class="page-head">
      <div>
        <h1>Заказы салона</h1>
        <p>Все заказы пользователей. Статус двигается только вперёд: новый → готов к выдаче → выдан.</p>
      </div>
      <a class="link-arrow" href="{{ route('catalog') }}">Открыть витрину</a>
    </header>

    @if ($errors->any())
      <div class="alert alert--error" role="alert">
        @foreach ($errors->all() as $error)
          <p>{{ $error }}</p>
        @endforeach
      </div>
    @endif

    <div class="admin-bar">
      <nav class="tabs" aria-label="Фильтр по статусу">
        <a href="{{ route('admin.orders.index') }}" class="{{ $status === null ? 'is-active' : '' }}">Все <b>{{ $counts['all'] }}</b></a>
        <a href="{{ route('admin.orders.index', ['status' => 'new']) }}" class="{{ $status === 'new' ? 'is-active' : '' }}">Новые <b>{{ $counts['new'] }}</b></a>
        <a href="{{ route('admin.orders.index', ['status' => 'ready']) }}" class="{{ $status === 'ready' ? 'is-active' : '' }}">Готовы <b>{{ $counts['ready'] }}</b></a>
        <a href="{{ route('admin.orders.index', ['status' => 'issued']) }}" class="{{ $status === 'issued' ? 'is-active' : '' }}">Выданы <b>{{ $counts['issued'] }}</b></a>
      </nav>

      <form method="GET" action="{{ route('admin.orders.index') }}" class="search">
        @if ($status)
          <input type="hidden" name="status" value="{{ $status }}">
        @endif
        <label class="sr-only" for="search">Поиск по логину или модели</label>
        <input type="search" id="search" name="search" value="{{ $search }}" placeholder="Логин или модель">
        <button type="submit" class="btn btn-small">Найти</button>
      </form>
    </div>

    @if ($orders->isEmpty())
      <div class="empty">
        <h2>Заказов нет</h2>
        <p>Измените фильтр или дождитесь первого заказа из каталога.</p>
      </div>
    @else
      <div class="table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th scope="col">№</th>
              <th scope="col">Клиент</th>
              <th scope="col">Часы</th>
              <th scope="col">Получение</th>
              <th scope="col">Оплата</th>
              <th scope="col" class="num">Сумма</th>
              <th scope="col">Статус</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($orders as $order)
              <tr>
                <td data-label="Заказ">
                  <b>№{{ $order->id }}</b>
                  <span class="cell-sub">{{ $order->created_at->translatedFormat('j.m.Y') }}</span>
                </td>
                <td data-label="Клиент">
                  {{ $order->user->fio }}
                  <span class="cell-sub">{{ $order->user->login }} · {{ $order->user->phone }}</span>
                </td>
                <td data-label="Часы">
                  {{ $order->watch->brand }} {{ $order->watch->model }}
                  <span class="cell-sub">{{ $order->quantity }} шт.</span>
                </td>
                <td data-label="Получение">{{ $order->pickup_date->translatedFormat('j.m.Y') }}</td>
                <td data-label="Оплата">{{ $order->paymentLabel() }}</td>
                <td data-label="Сумма" class="num price">{{ $order->totalLabel() }}</td>
                <td data-label="Статус">
                  <div class="cell-status">
                    @include('partials.status', ['status' => $order->status, 'label' => $order->statusLabel()])

                    @if ($order->canMoveTo('ready') || $order->canMoveTo('issued'))
                      <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="status-form">
                        @csrf
                        @method('PATCH')
                        <label class="sr-only" for="status-{{ $order->id }}">Новый статус заказа №{{ $order->id }}</label>
                        <select id="status-{{ $order->id }}" name="status">
                          @foreach (\App\Models\Order::TRANSITIONS[$order->status] as $next)
                            <option value="{{ $next }}">{{ \App\Models\Order::STATUSES[$next] }}</option>
                          @endforeach
                        </select>
                        <button type="submit" class="btn btn-small">Применить</button>
                      </form>
                    @else
                      <span class="cell-sub">Заказ закрыт</span>
                    @endif
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="pager">{{ $orders->links() }}</div>
    @endif
  </section>
@endsection
CODE,
    ],
    'app/Http/Controllers/ReviewController.php' => [
        'language' => 'php',
        'code' => <<<'CODE'
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless($order->isIssued(), 403, 'Отзыв можно оставить после выдачи заказа.');

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'text' => ['required', 'string', 'min:10', 'max:600'],
        ], [
            'rating.between' => 'Оценка — от 1 до 5.',
            'text.min' => 'Отзыв короче 10 символов.',
            'text.max' => 'Отзыв длиннее 600 символов.',
        ]);

        if ($order->review()->exists()) {
            return back()->withErrors(['text' => 'Отзыв по этому заказу уже оставлен.']);
        }

        $order->review()->create([
            'user_id' => $request->user()->id,
            'rating' => $data['rating'],
            'text' => $data['text'],
        ]);

        return redirect()->route('orders.index')
            ->with('success', 'Спасибо, отзыв опубликован в каталоге.');
    }
}
CODE,
    ],
    'routes/web.php' => [
        'language' => 'php',
        'code' => <<<'CODE'
<?php

use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'index'])->name('catalog');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

    Route::post('/orders/{order}/review', [ReviewController::class, 'store'])->name('reviews.store');
});

Route::middleware(['auth', EnsureUserIsAdmin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    });
CODE,
    ],
    'tests/Feature/OrderTest.php' => [
        'language' => 'php',
        'code' => <<<'CODE'
<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\Watch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $login = 'maria2027'): User
    {
        return User::firstOrCreate(['login' => $login], [
            'fio' => 'Иванова Мария Сергеевна',
            'phone' => '8(912)345-67-89',
            'email' => $login.'@example.ru',
            'password' => 'Student2027!',
            'role' => 'user',
        ]);
    }

    private function watch(array $overrides = []): Watch
    {
        return Watch::firstOrCreate(['slug' => 'polnoch'], array_merge([
            'brand' => 'Точный ход',
            'model' => 'Полночь',
            'price' => 48500,
            'case_mm' => 40,
            'movement' => 'automatic',
            'stock' => 3,
            'dial' => 'midnight',
            'summary' => 'Автоподзавод',
            'description' => 'Синий циферблат',
        ], $overrides));
    }

    private function payload(?Watch $watch = null, array $overrides = []): array
    {
        $watch ??= $this->watch();

        return array_merge([
            'watch_id' => $watch->id,
            'quantity' => 2,
            'pickup_date' => now()->addDays(2)->toDateString(),
            'payment' => 'onsite',
            'comment' => 'Подогнать браслет',
        ], $overrides);
    }

    public function test_order_total_is_calculated_on_server(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->post('/orders', $this->payload(null, ['total' => 1, 'price' => 1]))
            ->assertRedirect('/orders');

        $order = Order::firstOrFail();
        $this->assertSame(97000, $order->total);
        $this->assertSame('new', $order->status);
        $this->assertSame($user->id, $order->user_id);
    }

    public function test_stock_is_decreased_after_order(): void
    {
        $watch = $this->watch(['stock' => 5]);

        $this->actingAs($this->user())
            ->post('/orders', $this->payload($watch, ['quantity' => 2]));

        $this->assertSame(3, $watch->fresh()->stock);
    }

    public function test_cannot_order_more_than_stock(): void
    {
        $watch = $this->watch(['stock' => 1]);

        $this->actingAs($this->user())
            ->from('/orders/create')
            ->post('/orders', $this->payload($watch, ['quantity' => 2]))
            ->assertRedirect('/orders/create')
            ->assertSessionHasErrors('quantity');

        $this->assertSame(1, $watch->fresh()->stock);
        $this->assertSame(0, Order::count());
    }

    public function test_past_pickup_date_is_rejected(): void
    {
        $this->actingAs($this->user())
            ->from('/orders/create')
            ->post('/orders', $this->payload(null, ['pickup_date' => now()->subDay()->toDateString()]))
            ->assertSessionHasErrors('pickup_date');

        $this->assertSame(0, Order::count());
    }

    public function test_user_sees_only_own_orders(): void
    {
        $maria = $this->user();
        $dmitry = $this->user('dmitry2027');
        $watch = $this->watch();

        Order::create([
            'user_id' => $maria->id,
            'watch_id' => $watch->id,
            'quantity' => 1,
            'total' => $watch->price,
            'pickup_date' => now()->addDay()->toDateString(),
            'payment' => 'onsite',
            'status' => 'new',
            'comment' => 'Заказ Марии',
        ]);

        Order::create([
            'user_id' => $dmitry->id,
            'watch_id' => $watch->id,
            'quantity' => 1,
            'total' => $watch->price,
            'pickup_date' => now()->addDay()->toDateString(),
            'payment' => 'sbp',
            'status' => 'new',
            'comment' => 'Заказ Дмитрия',
        ]);

        $response = $this->actingAs($maria)->get('/orders');

        $response->assertOk();
        $response->assertSee('Заказ Марии');
        $response->assertDontSee('Заказ Дмитрия');
    }

    public function test_catalog_is_public_and_shows_watches(): void
    {
        $this->watch();

        $this->get('/')
            ->assertOk()
            ->assertSee('Полночь')
            ->assertSee('48 500');
    }
}
CODE,
    ],
    'README.md' => [
        'language' => 'md',
        'code' => <<<'CODE'
# Точный ход — часовой салон

Учебный проект по КИМ 09.02.07-3-2027: сайт салона часов с каталогом, заказами,
статусами, админкой и отзывами. Сделан на Laravel 13 + SQLite + Blade, без сборщика
и внешних CDN — запускается офлайн.

## Что умеет

- Каталог из шести моделей: цена, корпус, механизм, остаток на складе, средний рейтинг.
- Регистрация и вход по логину. Пароль — от 8 символов, с буквой и цифрой.
- Оформление заказа: модель, количество 1–3, дата получения, оплата, комментарий.
- Сумма считается на сервере, остаток списывается в транзакции с блокировкой строки.
- Личный кабинет: свои заказы и текущий статус, пагинация по 8 записей.
- Админка: все заказы, фильтр по статусу, поиск по логину и модели, смена статуса.
- Статусы двигаются только вперёд: «Новый» → «Готов к выдаче» → «Выдан».
- Отзыв можно оставить только по выданному заказу и только один раз.

## Запуск

```bash
composer install
copy .env.example .env
php artisan key:generate
type nul > database\database.sqlite
php artisan migrate:fresh --seed
php artisan serve --port=8200
```

Открыть `http://127.0.0.1:8200`.

Требования: PHP 8.3+ с расширениями `pdo_sqlite`, `mbstring`, `openssl`; Composer.
Проект проверен на PHP 8.5.4.

## Демо-аккаунты

| Роль | Логин | Пароль |
| --- | --- | --- |
| Администратор | `TikTak2027` | `Demo77` |
| Студент | `maria2027` | `Student2027!` |
| Студент | `dmitry2027` | `Student2027!` |
| Студент | `olga2027` | `Student2027!` |

После `migrate:fresh --seed` в базе 6 моделей, 3 пользователя-студента и 5 заказов
в разных статусах: есть что показать в кабинете и в админке сразу.

## Сценарий проверки

1. Войти как `maria2027` → «Мои заказы»: видны заказы в статусах «Готов к выдаче»
   и «Выдан».
2. «Новый заказ» → выбрать модель, количество 2, дату, оплату по СБП → оформить.
   Появится «Заказ №N принят», сумма пересчитана сервером.
3. Выйти, войти как `TikTak2027` → «Админка»: найти заказ по логину, перевести
   «Новый» → «Готов к выдаче» → «Выдан».
4. Войти обратно студентом: у выданного заказа раскрывается форма отзыва.
   Оставить отзыв → он появляется в каталоге в блоке «Что говорят владельцы».
5. Попробовать открыть `/admin/orders` под студентом — 403.
6. Попробовать перевести заказ сразу из «Новый» в «Выдан» — ошибка валидации.

## Соответствие КИМ

| Блок КИМ | Баллы | Где в проекте |
| --- | --- | --- |
| Регистрация: ФИО, логин, телефон, email, пароль | 6 | `AuthController::register`, `auth/register.blade.php` |
| Валидация полей и уникальность | 16 | правила `regex`, `unique`, `Password::min(8)` |
| Вход, выход, сессия, ограничение попыток | 3 | `AuthController::login`, `RateLimiter` |
| Роли: пользователь и администратор | 11 | `users.role`, middleware `admin` |
| Каталог и карточка товара | 10 | `CatalogController`, `catalog.blade.php` |
| Оформление заказа | 4 | `OrderController::store`, `orders/create.blade.php` |
| Три статуса и переходы | 15 | `Order::TRANSITIONS`, `Admin\OrderController` |
| Два способа оплаты | 10 | `Order::PAYMENTS`, поле `payment` |
| Отзывы после выдачи | — | `ReviewController`, блок отзывов в каталоге |
| Адаптивность 390/768/1440 | — | `public/css/app.css`, медиазапросы |
| Защита: CSRF, 403, серверная сумма | — | `@csrf`, middleware, расчёт в контроллере |

## Структура

```
app/Http/Controllers/       Auth, Catalog, Order, Review, Admin\Order
app/Http/Middleware/        EnsureUserIsAdmin
app/Models/                 User, Watch, Order, Review
app/Support/Text.php        русские формы слова после числа
database/migrations/        users, watches, orders, reviews
database/seeders/           User, Watch, Order
resources/views/            layouts, catalog, auth, orders, admin, partials, errors
public/css/app.css          дизайн-токены и адаптив
public/js/app.js            живые часы и предпросмотр суммы
public/fonts/               Cormorant Garamond и Golos Text локально
tests/Feature/              Auth, Order, AdminOrder, Review
```

## Тесты

```bash
php artisan test
```

23 теста, 77 проверок: регистрация и валидация, вход и выход, доступ к админке,
серверный расчёт суммы, списание остатка, запрет заказа больше остатка, запрет
прошлой даты, изоляция заказов, переходы статусов, отзывы.

## Оговорка

Названия моделей, цены, адрес салона и отзывы — демонстрационные, придуманы для
учебного проекта. Совпадения с реальными магазинами случайны.
CODE,
    ],
];
