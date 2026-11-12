window.RECIPES = [
  {
    id: 'validation-register',
    title: 'Валидация регистрации по требованиям КИМ',
    when: 'Нужны логин из латиницы и цифр, пароль от 8 символов, ФИО кириллицей, телефон `8(XXX)XXX-XX-XX` и русские сообщения у полей.',
    tags: ['авторизация', 'валидация'],
    lang: 'php',
    file: 'app/Http/Controllers/AuthController.php',
    code: `$data = $request->validate([
    'fio' => ['required', 'string', 'regex:/^[А-ЯЁа-яё\\s-]+$/u', 'max:255'],
    'login' => ['required', 'regex:/^[a-zA-Z0-9]{6,}$/', 'unique:users,login', 'max:50'],
    'phone' => ['required', 'regex:/^8\\(\\d{3}\\)\\d{3}-\\d{2}-\\d{2}$/'],
    'email' => ['required', 'email', 'unique:users,email', 'max:255'],
    'password' => ['required', 'string', 'min:8', 'max:255'],
], [
    'fio.required' => 'ФИО обязательно для заполнения.',
    'fio.regex' => 'ФИО должно содержать только кириллицу, пробелы и дефис.',
    'login.required' => 'Логин обязателен.',
    'login.regex' => 'Логин: латинские буквы и цифры, не менее 6 символов.',
    'login.unique' => 'Пользователь с таким логином уже существует.',
    'phone.required' => 'Телефон обязателен.',
    'phone.regex' => 'Телефон должен быть в формате 8(XXX)XXX-XX-XX.',
    'email.required' => 'Email обязателен.',
    'email.email' => 'Введите корректный email.',
    'email.unique' => 'Пользователь с таким email уже существует.',
    'password.required' => 'Пароль обязателен.',
    'password.min' => 'Пароль должен быть не менее 8 символов.',
]);`,
    after: 'Не забудьте `@error` у каждого поля и `old()` для сохранения введённого.'
  },
  {
    id: 'hash-login',
    title: 'Хеширование и вход через Auth::attempt',
    when: 'Нужно хранить пароль безопасно и пускать пользователя по логину, а не по email.',
    tags: ['авторизация', 'безопасность'],
    lang: 'php',
    file: 'AuthController.php',
    code: `use Illuminate\\Support\\Facades\\Auth;
use Illuminate\\Support\\Facades\\Hash;

// Регистрация
User::create([
    'fio' => $data['fio'],
    'login' => $data['login'],
    'phone' => $data['phone'],
    'email' => $data['email'],
    'password' => Hash::make($data['password']),
    'role' => 'user',
]);

// Вход
$credentials = $request->validate([
    'login' => ['required', 'string'],
    'password' => ['required', 'string'],
]);

if (!Auth::attempt($credentials)) {
    return back()
        ->withErrors(['login' => 'Неверный логин или пароль.'])
        ->withInput();
}

$request->session()->regenerate();

return auth()->user()->isAdmin()
    ? redirect()->route('admin')
    : redirect()->route('profile');`,
    after: '`Auth::attempt` сам сверит хеш. Не сравнивайте пароли строками.'
  },
  {
    id: 'admin-middleware',
    title: 'Middleware администратора',
    when: 'Нужно закрыть `/admin` так, чтобы обычный пользователь получил 403, а не увидел панель.',
    tags: ['авторизация', 'безопасность'],
    lang: 'php',
    file: 'app/Http/Middleware/AdminMiddleware.php',
    code: `public function handle(Request $request, Closure $next): Response
{
    if (!auth()->check() || auth()->user()->role !== 'admin') {
        abort(403, 'Доступ запрещён.');
    }

    return $next($request);
}

// bootstrap/app.php (Laravel 11+)
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'admin' => \\App\\Http\\Middleware\\AdminMiddleware::class,
    ]);
})

// routes/web.php
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin');
});`,
    after: 'Проверьте вручную: войдите пользователем и откройте `/admin` по прямому URL.'
  },
  {
    id: 'owner-check',
    title: 'Проверка владельца записи',
    when: 'Пользователь может открыть чужую заявку или отправить отзыв по чужому ID.',
    tags: ['безопасность'],
    lang: 'php',
    file: 'ApplicationController.php',
    code: `public function storeReview(Request $request, Application $application)
{
    if ($application->user_id !== auth()->id()) {
        abort(403, 'Доступ запрещён.');
    }

    if ($application->status !== 'completed') {
        return back()->with('error', 'Отзыв доступен только после завершения.');
    }

    // ...
}`,
    after: 'Одной проверки в шаблоне недостаточно: POST можно отправить руками.'
  },
  {
    id: 'routes-auth',
    title: 'Маршруты регистрации, входа и кабинета',
    when: 'Нужен полный набор URL и имена, на которые ссылаются шаблоны.',
    tags: ['маршруты'],
    lang: 'php',
    file: 'routes/web.php',
    code: `Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'storeRegister'])->name('register.store');

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'storeLogin'])->name('login.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ApplicationController::class, 'index'])->name('profile');
    Route::get('/application', [ApplicationController::class, 'create'])->name('application');
    Route::post('/application', [ApplicationController::class, 'store'])->name('application.store');
    Route::post('/application/{application}/review', [ApplicationController::class, 'storeReview'])
        ->name('application.review');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin');
    Route::post('/application/{application}/status', [AdminController::class, 'updateStatus'])
        ->name('admin.status');
});`,
    after: 'Маршрут входа должен называться `login`, иначе middleware `auth` не найдёт, куда перенаправить.'
  },
  {
    id: 'migration-application',
    title: 'Миграция заявки со статусами',
    when: 'Нужна таблица заявок со связями, оплатой, статусом и отзывом.',
    tags: ['бд'],
    lang: 'php',
    file: 'database/migrations/..._create_applications_table.php',
    code: `Schema::create('applications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('room_id')->constrained()->cascadeOnDelete();
    $table->date('start_date');
    $table->enum('payment_type', ['in_person', 'sbp'])->default('in_person');
    $table->enum('status', ['new', 'assigned', 'completed'])->default('new');
    $table->unsignedTinyInteger('rating')->nullable();
    $table->text('review')->nullable();
    $table->index('status');
    $table->timestamps();
});`,
    after: 'Порядок миграций: сначала `users` и `rooms`, потом `applications`.'
  },
  {
    id: 'model-labels',
    title: 'Модель со связями и русскими подписями',
    when: 'В Blade нужны `status_label` и `payment_label`, а не английские значения из базы.',
    tags: ['бд', 'eloquent'],
    lang: 'php',
    file: 'app/Models/Application.php',
    code: `class Application extends Model
{
    protected $fillable = [
        'user_id', 'room_id', 'start_date', 'payment_type',
        'status', 'rating', 'review',
    ];

    protected $casts = [
        'start_date' => 'date',
        'rating' => 'integer',
    ];

    public const STATUS_LABELS = [
        'new' => 'Новая',
        'assigned' => 'Мероприятие назначено',
        'completed' => 'Завершено',
    ];

    public const PAYMENT_LABELS = [
        'in_person' => 'При очном посещении',
        'sbp' => 'Переводом по системе СБП',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getPaymentLabelAttribute(): string
    {
        return self::PAYMENT_LABELS[$this->payment_type] ?? $this->payment_type;
    }
}`,
    after: 'В шаблоне: `{{ $application->status_label }}`.'
  },
  {
    id: 'blade-layout',
    title: 'Layout с меню по ролям и flash-сообщениями',
    when: 'Нужна одна шапка для всех страниц и разные пункты меню для гостя, пользователя и админа.',
    tags: ['blade'],
    lang: 'blade',
    file: 'resources/views/layouts/app.blade.php',
    code: `<body>
    <header class="site-header">
        <a href="{{ route('home') }}">Конференции.РФ</a>

        <nav aria-label="Основное меню">
            @auth
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin') }}">Панель</a>
                @else
                    <a href="{{ route('profile') }}">Мои заявки</a>
                    <a href="{{ route('application') }}">Новая заявка</a>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="link-button">Выйти</button>
                </form>
            @else
                <a href="{{ route('login') }}">Вход</a>
                <a href="{{ route('register') }}">Регистрация</a>
            @endauth
        </nav>
    </header>

    <main class="container py-4">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>
</body>`,
    after: 'Не забудьте `@extends(\'layouts.app\')` и `@section(\'content\')` в дочерних шаблонах.'
  },
  {
    id: 'blade-form',
    title: 'Форма с old(), @error и @csrf',
    when: 'Нужно, чтобы после ошибки значения сохранялись, а сообщения стояли у полей.',
    tags: ['blade', 'формы'],
    lang: 'blade',
    file: 'resources/views/register.blade.php',
    code: `<form method="POST" action="{{ route('register.store') }}" novalidate>
    @csrf

    <div class="mb-3">
        <label for="login" class="form-label">Логин</label>
        <input type="text" id="login" name="login"
               class="form-control @error('login') is-invalid @enderror"
               value="{{ old('login') }}"
               autocomplete="username" required>
        @error('login')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Пароль</label>
        <input type="password" id="password" name="password"
               class="form-control @error('password') is-invalid @enderror"
               autocomplete="new-password" minlength="8" required>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary w-100">Создать пользователя</button>
</form>`,
    after: '`novalidate` отключает браузерную проверку, чтобы были видны сообщения Laravel.'
  },
  {
    id: 'upload-image',
    title: 'Загрузка изображения и удаление старого',
    when: 'Нужно сохранить картинку в storage, показать её и не оставить мусор при замене.',
    tags: ['файлы'],
    lang: 'php',
    file: 'PostController.php',
    code: `use Illuminate\\Support\\Facades\\Storage;

public function store(StorePostRequest $request)
{
    $data = $request->validated();

    if ($request->hasFile('image')) {
        $data['image'] = $request->file('image')->store('posts', 'public');
    }

    Post::create($data);

    return redirect()->route('admin.posts.index')
        ->with('success', 'Пост создан.');
}

public function update(UpdatePostRequest $request, Post $post)
{
    $data = $request->validated();

    if ($request->hasFile('image')) {
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $data['image'] = $request->file('image')->store('posts', 'public');
    }

    $post->update($data);

    return back()->with('success', 'Пост обновлён.');
}`,
    after: 'В форме нужен `enctype="multipart/form-data"`, в шаблоне — `asset(\'storage/\' . $post->image)`, один раз — `php artisan storage:link`.'
  },
  {
    id: 'status-update',
    title: 'Смена статуса администратором',
    when: 'Нужна кнопка, которая переводит заявку в «Назначено» или «Завершено» и сохраняет результат.',
    tags: ['crud', 'админка'],
    lang: 'php',
    file: 'AdminController.php',
    code: `public function updateStatus(Request $request, Application $application)
{
    $data = $request->validate([
        'status' => ['required', 'in:new,assigned,completed'],
    ], [
        'status.required' => 'Выберите статус.',
        'status.in' => 'Недопустимый статус.',
    ]);

    $application->update($data);

    return back()->with('success', 'Статус изменён на «' . $application->status_label . '».');
}

// Blade
<form method="POST" action="{{ route('admin.status', $application) }}">
    @csrf
    <select name="status" class="form-select">
        @foreach (\\App\\Models\\Application::STATUS_LABELS as $value => $label)
            <option value="{{ $value }}" @selected($application->status === $value)>
                {{ $label }}
            </option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary">Сохранить</button>
</form>`,
    after: 'Статус меняется только POST и только у админа. Ссылка `?status=...` не подходит.'
  },
  {
    id: 'filter-search',
    title: 'Фильтр, поиск и пагинация',
    when: 'В админке нужны фильтр по статусу, поиск по ФИО и страницы без потери фильтра.',
    tags: ['crud', 'админка'],
    lang: 'php',
    file: 'AdminController.php',
    code: `$query = Application::with(['user', 'room']);

$query->when($request->filled('status'), function ($q) use ($request) {
    $q->where('status', $request->status);
});

$query->when($request->filled('search'), function ($q) use ($request) {
    $search = trim($request->search);

    $q->where(function ($sub) use ($search) {
        $sub->whereHas('user', function ($u) use ($search) {
            $u->where('fio', 'like', "%{$search}%")
              ->orWhere('login', 'like', "%{$search}%");
        })->orWhereHas('room', function ($r) use ($search) {
            $r->where('name', 'like', "%{$search}%");
        });
    });
});

$applications = $query->orderByDesc('created_at')
    ->paginate(10)
    ->withQueryString();`,
    after: '`withQueryString()` сохраняет фильтры при переходе на страницу 2.'
  },
  {
    id: 'phone-mask',
    title: 'Маска телефона на JavaScript',
    when: 'Нужно, чтобы пользователь вводил `8(999)123-45-67`, не расставляя скобки вручную.',
    tags: ['интерфейс', 'js'],
    lang: 'javascript',
    file: 'public/js/app.js',
    code: `const phone = document.querySelector('#phone');

if (phone) {
    phone.addEventListener('input', () => {
        let digits = phone.value.replace(/\\D/g, '').slice(0, 11);

        if (digits.startsWith('7')) {
            digits = '8' + digits.slice(1);
        }

        let out = digits;

        if (digits.length > 1) {
            out = digits[0] + '(' + digits.slice(1, 4);
        }
        if (digits.length >= 4) {
            out += ')' + digits.slice(4, 7);
        }
        if (digits.length >= 7) {
            out += '-' + digits.slice(7, 9);
        }
        if (digits.length >= 9) {
            out += '-' + digits.slice(9, 11);
        }

        phone.value = out;
    });
}`,
    after: 'Серверная регулярка всё равно нужна: маску можно обойти.'
  },
  {
    id: 'rating-stars',
    title: 'Рейтинг звёздами',
    when: 'Нужно выбрать оценку от 1 до 5 и отправить её вместе с отзывом.',
    tags: ['интерфейс', 'js'],
    lang: 'javascript',
    file: 'public/js/app.js',
    code: `document.querySelectorAll('[data-rating]').forEach((picker) => {
    const input = document.querySelector(picker.dataset.rating);
    const stars = [...picker.querySelectorAll('button')];

    const paint = (value) => {
        stars.forEach((star, index) => {
            star.classList.toggle('is-active', index < value);
            star.setAttribute('aria-pressed', String(index < value));
        });
    };

    stars.forEach((star, index) => {
        star.addEventListener('click', () => {
            input.value = index + 1;
            paint(index + 1);
        });
    });

    paint(Number(input.value) || 0);
});`,
    after: 'Скрытое поле `input type="hidden" name="rating"` должно иметь `id`, на который ссылается `data-rating`.'
  },
  {
    id: 'seeder-admin',
    title: 'Сидер администратора',
    when: 'После `migrate:fresh` нужен гарантированный вход с логином и паролем из задания.',
    tags: ['бд', 'авторизация'],
    lang: 'php',
    file: 'database/seeders/UserSeeder.php',
    code: `use Illuminate\\Support\\Facades\\Hash;

User::updateOrCreate(
    ['login' => 'Conf2027'],
    [
        'fio' => 'Администратор Портала',
        'phone' => '8(999)000-00-00',
        'email' => 'admin@conferences.ru',
        'password' => Hash::make('Demo77'),
        'role' => 'admin',
    ]
);`,
    after: 'Логин и пароль берите дословно из задания. Не храните их открытым текстом в базе.'
  },
  {
    id: 'empty-state',
    title: 'Пустое состояние списка',
    when: 'У пользователя ещё нет заявок, а страница не должна выглядеть пустой.',
    tags: ['интерфейс', 'blade'],
    lang: 'blade',
    file: 'resources/views/profile.blade.php',
    code: `@forelse ($applications as $application)
    <article class="card mb-3">
        <div class="card-body">
            <h2 class="h5">{{ $application->room->name }}</h2>
            <span class="badge">{{ $application->status_label }}</span>
        </div>
    </article>
@empty
    <div class="alert alert-info">
        У вас пока нет заявок.
        <a href="{{ route('application') }}">Создайте первую</a>.
    </div>
@endforelse`,
    after: 'Пустое состояние — часть интерфейса. Не оставляйте пустую таблицу с заголовками.'
  },
  {
    id: 'micro-motion',
    title: 'Микроанимации и reduced motion',
    when: 'Нужны аккуратные hover-эффекты и появление алертов, которые отключаются системной настройкой.',
    tags: ['интерфейс', 'css'],
    lang: 'css',
    file: 'public/css/app.css',
    code: `.btn {
    transition: background-color .18s ease, transform .18s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.card {
    transition: border-color .18s ease, transform .18s ease;
}

.card:hover {
    border-color: #007bff;
    transform: translateY(-2px);
}

@keyframes fade-up {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.alert {
    animation: fade-up .25s ease-out both;
}

@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        animation-duration: .01ms !important;
        transition-duration: .01ms !important;
        scroll-behavior: auto !important;
    }
}`,
    after: 'Анимируйте только `transform` и `opacity`. Не трогайте `width` и `height`.'
  },
  {
    id: 'optimize-image',
    title: 'Оптимизация изображения на PHP',
    when: 'Нужно уменьшить загруженную картинку до 1200 px и пересохранить JPEG.',
    tags: ['файлы', 'изображения'],
    lang: 'php',
    file: 'app/Support/ImageOptimizer.php',
    code: `function resizeImage(string $source, string $target, int $maxWidth = 1200): void
{
    [$width, $height, $type] = getimagesize($source);

    if ($width <= $maxWidth) {
        copy($source, $target);
        return;
    }

    $ratio = $maxWidth / $width;
    $newWidth = $maxWidth;
    $newHeight = (int) round($height * $ratio);

    $src = match ($type) {
        IMAGETYPE_JPEG => imagecreatefromjpeg($source),
        IMAGETYPE_PNG => imagecreatefrompng($source),
        IMAGETYPE_WEBP => imagecreatefromwebp($source),
        default => throw new RuntimeException('Неподдерживаемый формат'),
    };

    $dst = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    imagejpeg($dst, $target, 82);
    imagedestroy($src);
    imagedestroy($dst);
}`,
    after: 'Для PNG с прозрачностью сохраняйте PNG, иначе фон станет чёрным.'
  }
];
