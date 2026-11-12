<?php

namespace Database\Seeders;

use App\Models\Drill;
use Illuminate\Database\Seeder;

class DrillSeeder extends Seeder
{
    public function run(): void
    {
        $position = 0;

        $drills = array_merge(
            $this->html(),
            $this->css(),
            $this->bootstrap(),
            $this->php(),
            $this->sql(),
            $this->laravel(),
        );

        foreach ($drills as $drill) {
            $this->save($drill, ++$position);
        }

        $this->command?->info('Тренажёр: вариантов — '.Drill::count().'.');
    }

    private function save(array $drill, int $position): void
    {
        Drill::updateOrCreate(
            ['slug' => $drill['slug']],
            [
                'track' => $drill['track'],
                'title' => $drill['title'],
                'level' => $drill['level'] ?? 'средний',
                'minutes' => $drill['minutes'] ?? 15,
                'prompt' => $drill['prompt'],
                'requirements' => $drill['requirements'],
                'starter_code' => $drill['starter_code'] ?? null,
                'language' => $drill['language'],
                'checks' => $drill['checks'],
                'solution' => $drill['solution'],
                'position' => $position,
                'is_published' => true,
            ]
        );
    }

    private function html(): array
    {
        $themes = [
            [
                'slug' => 'conference-card',
                'title' => 'Карточка заявки на конференцию',
                'event' => 'Конференция «Цифра-2027»',
                'statusClass' => 'status--new',
                'statusLabel' => 'Новая заявка',
                'date' => '2026-11-12',
                'dateHuman' => '12.11.2026',
                'fields' => [
                    ['Дата начала', '12.11.2026'],
                    ['Помещение', 'Большой зал'],
                    ['Оплата', 'По счёту'],
                ],
            ],
            [
                'slug' => 'medcenter-card',
                'title' => 'Карточка записи в медцентр',
                'event' => 'Приём кардиолога',
                'statusClass' => 'status--assigned',
                'statusLabel' => 'Подтверждена',
                'date' => '2026-12-03',
                'dateHuman' => '03.12.2026',
                'fields' => [
                    ['Дата приёма', '03.12.2026'],
                    ['Врач', 'Соколова А. П.'],
                    ['Кабинет', '214'],
                ],
            ],
            [
                'slug' => 'autoservice-card',
                'title' => 'Карточка заявки в автосервис',
                'event' => 'Ремонт подвески',
                'statusClass' => 'status--in-work',
                'statusLabel' => 'В работе',
                'date' => '2026-11-20',
                'dateHuman' => '20.11.2026',
                'fields' => [
                    ['Дата', '20.11.2026'],
                    ['Автомобиль', 'Lada Vesta, А123ВС'],
                    ['Работы', 'Диагностика и замена стоек'],
                ],
            ],
            [
                'slug' => 'rent-card',
                'title' => 'Карточка брони помещения',
                'event' => 'Аренда переговорной',
                'statusClass' => 'status--completed',
                'statusLabel' => 'Завершена',
                'date' => '2026-10-15',
                'dateHuman' => '15.10.2026',
                'fields' => [
                    ['Дата', '15.10.2026'],
                    ['Объект', 'Переговорная «Вектор»'],
                    ['Срок', '2 часа'],
                ],
            ],
            [
                'slug' => 'courses-card',
                'title' => 'Карточка заявки на курс',
                'event' => 'Курс «PHP с нуля»',
                'statusClass' => 'status--rejected',
                'statusLabel' => 'Отклонена',
                'date' => '2026-09-01',
                'dateHuman' => '01.09.2026',
                'fields' => [
                    ['Старт группы', '01.09.2026'],
                    ['Курс', 'PHP с нуля'],
                    ['Формат', 'Онлайн'],
                ],
            ],
        ];

        $drills = [];

        foreach ($themes as $theme) {
            $meta = '';
            $checks = [
                ['type' => 'contains', 'label' => 'Корневой тег article', 'value' => '<article'],
                ['type' => 'contains', 'label' => 'Класс карточки', 'value' => 'class="application"'],
                ['type' => 'contains', 'label' => 'Шапка карточки', 'value' => 'application-head'],
                ['type' => 'contains', 'label' => 'Модификатор статуса', 'value' => $theme['statusClass']],
                ['type' => 'contains', 'label' => 'Дата в атрибуте datetime', 'value' => 'datetime="'.$theme['date'].'"'],
                ['type' => 'contains', 'label' => 'Список характеристик', 'value' => '<dl'],
            ];

            foreach ($theme['fields'] as [$label, $value]) {
                $meta .= "    <div>\n      <dt>{$label}</dt>\n      <dd>{$value}</dd>\n    </div>\n";

                $checks[] = ['type' => 'contains', 'label' => 'Поле «'.$label.'»', 'value' => '<dt>'.$label.'</dt>'];
                $checks[] = ['type' => 'contains', 'label' => 'Значение «'.$value.'»', 'value' => '<dd>'.$value.'</dd>'];
            }

            $solution = "<article class=\"application\">\n"
                ."  <header class=\"application-head\">\n"
                ."    <div>\n"
                ."      <h2>{$theme['event']}</h2>\n"
                ."      <span class=\"status {$theme['statusClass']}\">{$theme['statusLabel']}</span>\n"
                ."    </div>\n"
                ."    <time datetime=\"{$theme['date']}\">{$theme['dateHuman']}</time>\n"
                ."  </header>\n\n"
                ."  <dl class=\"application-meta\">\n"
                .$meta
                ."  </dl>\n"
                ."</article>";

            $drills[] = [
                'slug' => $theme['slug'],
                'track' => 'html',
                'title' => $theme['title'],
                'level' => 'легкий',
                'minutes' => 12,
                'prompt' => 'Соберите карточку заявки для сайта «'.$theme['event'].'». Нужна семантическая разметка: статья, шапка со статусом и датой, список характеристик.',
                'requirements' => [
                    'Корневой тег — `<article class="application">`.',
                    'Шапка — `<header class="application-head">` с названием `h2`.',
                    'Статус — `<span class="status '.$theme['statusClass'].'">'.$theme['statusLabel'].'</span>`.',
                    'Дата — `<time datetime="'.$theme['date'].'">'.$theme['dateHuman'].'</time>`.',
                    'Характеристики — `<dl class="application-meta">` с тремя парами `dt`/`dd`.',
                ],
                'starter_code' => "<article class=\"application\">\n  <!-- Шапка: название, статус, дата -->\n  <!-- Характеристики: три пары dt/dd -->\n</article>",
                'language' => 'html',
                'checks' => ['checks' => $checks],
                'solution' => $solution,
            ];
        }

        return $drills;
    }

    private function css(): array
    {
        $variants = [
            ['slug' => 'grid-2-3', 'title' => 'Сетка 1 → 2 → 3', 'tablet' => 640, 'desktop' => 1024, 'tabletCols' => 2, 'desktopCols' => 3, 'gap' => 20],
            ['slug' => 'grid-2-4', 'title' => 'Сетка 1 → 2 → 4', 'tablet' => 600, 'desktop' => 1100, 'tabletCols' => 2, 'desktopCols' => 4, 'gap' => 16],
            ['slug' => 'grid-2-3-wide', 'title' => 'Сетка 1 → 2 → 3 с широким шагом', 'tablet' => 768, 'desktop' => 1200, 'tabletCols' => 2, 'desktopCols' => 3, 'gap' => 24],
            ['slug' => 'grid-3-4', 'title' => 'Сетка 1 → 3 → 4', 'tablet' => 720, 'desktop' => 1180, 'tabletCols' => 3, 'desktopCols' => 4, 'gap' => 18],
            ['slug' => 'grid-2-2', 'title' => 'Сетка 1 → 2 → 2', 'tablet' => 560, 'desktop' => 960, 'tabletCols' => 2, 'desktopCols' => 2, 'gap' => 12],
        ];

        $drills = [];

        foreach ($variants as $v) {
            $solution = ".cards {\n"
                ."    display: grid;\n"
                ."    gap: {$v['gap']}px;\n"
                ."    grid-template-columns: 1fr;\n"
                ."}\n\n"
                ."@media (min-width: {$v['tablet']}px) {\n"
                ."    .cards {\n"
                ."        grid-template-columns: repeat({$v['tabletCols']}, 1fr);\n"
                ."    }\n"
                ."}\n\n"
                ."@media (min-width: {$v['desktop']}px) {\n"
                ."    .cards {\n"
                ."        grid-template-columns: repeat({$v['desktopCols']}, 1fr);\n"
                ."    }\n"
                ."}";

            $drills[] = [
                'slug' => $v['slug'],
                'track' => 'css',
                'title' => $v['title'],
                'level' => 'легкий',
                'minutes' => 12,
                'prompt' => 'Сверстайте адаптивную сетку карточек `.cards`: одна колонка на телефоне, '.$v['tabletCols'].' на планшете от '.$v['tablet'].' px, '.$v['desktopCols'].' на десктопе от '.$v['desktop'].' px. Отступ между карточками — '.$v['gap'].' px.',
                'requirements' => [
                    'На мобильном — `grid-template-columns: 1fr`.',
                    '`display: grid` и `gap: '.$v['gap'].'px`.',
                    'Медиазапрос `@media (min-width: '.$v['tablet'].'px)` с `repeat('.$v['tabletCols'].', 1fr)`.',
                    'Медиазапрос `@media (min-width: '.$v['desktop'].'px)` с `repeat('.$v['desktopCols'].', 1fr)`.',
                ],
                'starter_code' => ".cards {\n    /* сетка */\n}",
                'language' => 'css',
                'checks' => [
                    'checks' => [
                        ['type' => 'contains', 'label' => 'display: grid', 'value' => 'display: grid'],
                        ['type' => 'contains', 'label' => 'Отступ сетки', 'value' => 'gap: '.$v['gap'].'px'],
                        ['type' => 'contains', 'label' => 'Одна колонка на мобильном', 'value' => 'grid-template-columns: 1fr'],
                        ['type' => 'contains', 'label' => 'Планшетный медиазапрос', 'value' => '@media (min-width: '.$v['tablet'].'px)'],
                        ['type' => 'contains', 'label' => 'Планшетные колонки', 'value' => 'repeat('.$v['tabletCols'].', 1fr)'],
                        ['type' => 'contains', 'label' => 'Десктопный медиазапрос', 'value' => '@media (min-width: '.$v['desktop'].'px)'],
                        ['type' => 'contains', 'label' => 'Десктопные колонки', 'value' => 'repeat('.$v['desktopCols'].', 1fr)'],
                    ],
                ],
                'solution' => $solution,
            ];
        }

        return $drills;
    }

    private function bootstrap(): array
    {
        $forms = [
            [
                'slug' => 'bootstrap-register',
                'title' => 'Форма регистрации на Bootstrap',
                'action' => "route('register.store')",
                'button' => 'Зарегистрироваться',
                'fields' => [
                    ['name' => 'name', 'type' => 'text', 'label' => 'Имя'],
                    ['name' => 'email', 'type' => 'email', 'label' => 'Email'],
                    ['name' => 'password', 'type' => 'password', 'label' => 'Пароль'],
                    ['name' => 'password_confirmation', 'type' => 'password', 'label' => 'Повтор пароля'],
                ],
            ],
            [
                'slug' => 'bootstrap-application',
                'title' => 'Форма заявки с выбором помещения',
                'action' => "route('applications.store')",
                'button' => 'Отправить заявку',
                'fields' => [
                    ['name' => 'room_id', 'type' => 'select', 'label' => 'Помещение', 'options' => ['Большой зал', 'Переговорная', 'Коворкинг']],
                    ['name' => 'start_date', 'type' => 'date', 'label' => 'Дата начала'],
                    ['name' => 'payment_type', 'type' => 'select', 'label' => 'Оплата', 'options' => ['Наличные', 'Карта', 'СБП']],
                ],
            ],
            [
                'slug' => 'bootstrap-login',
                'title' => 'Форма входа с ошибками',
                'action' => "route('login.store')",
                'button' => 'Войти',
                'fields' => [
                    ['name' => 'email', 'type' => 'email', 'label' => 'Email'],
                    ['name' => 'password', 'type' => 'password', 'label' => 'Пароль'],
                    ['name' => 'remember', 'type' => 'checkbox', 'label' => 'Запомнить меня'],
                ],
            ],
            [
                'slug' => 'bootstrap-review',
                'title' => 'Форма отзыва с оценкой',
                'action' => "route('reviews.store')",
                'button' => 'Оставить отзыв',
                'fields' => [
                    ['name' => 'rating', 'type' => 'select', 'label' => 'Оценка', 'options' => ['5', '4', '3', '2', '1']],
                    ['name' => 'review', 'type' => 'textarea', 'label' => 'Отзыв'],
                ],
            ],
            [
                'slug' => 'bootstrap-profile',
                'title' => 'Форма профиля с телефоном',
                'action' => "route('profile.update')",
                'button' => 'Сохранить',
                'fields' => [
                    ['name' => 'name', 'type' => 'text', 'label' => 'Имя'],
                    ['name' => 'phone', 'type' => 'tel', 'label' => 'Телефон'],
                    ['name' => 'email', 'type' => 'email', 'label' => 'Email'],
                ],
            ],
            [
                'slug' => 'bootstrap-book-card',
                'title' => 'Карточка книги с радио-кнопками',
                'action' => "route('cards.store')",
                'button' => 'Отправить карточку',
                'fields' => [
                    ['name' => 'author', 'type' => 'text', 'label' => 'Автор книги'],
                    ['name' => 'title', 'type' => 'text', 'label' => 'Название книги'],
                    ['name' => 'type', 'type' => 'radio', 'label' => 'Тип карточки', 'options' => ['share' => 'Готов поделиться', 'want' => 'Хочу в свою библиотеку']],
                    ['name' => 'publisher', 'type' => 'text', 'label' => 'Издательство'],
                    ['name' => 'year', 'type' => 'number', 'label' => 'Год издания'],
                    ['name' => 'binding', 'type' => 'select', 'label' => 'Переплёт', 'options' => ['Твёрдый', 'Мягкий']],
                    ['name' => 'condition', 'type' => 'select', 'label' => 'Состояние', 'options' => ['Идеальное', 'Нормальное', 'Требует внимания', 'Годится подпирать ножку стола']],
                ],
            ],
            [
                'slug' => 'bootstrap-booking',
                'title' => 'Бронирование столика',
                'action' => "route('bookings.store')",
                'button' => 'Забронировать',
                'fields' => [
                    ['name' => 'visit_date', 'type' => 'date', 'label' => 'Дата'],
                    ['name' => 'visit_time', 'type' => 'time', 'label' => 'Время'],
                    ['name' => 'guests', 'type' => 'number', 'label' => 'Гостей'],
                    ['name' => 'phone', 'type' => 'tel', 'label' => 'Телефон'],
                ],
            ],
            [
                'slug' => 'bootstrap-pizza-order',
                'title' => 'Заказ пиццы с «Иной пиццей»',
                'action' => "route('orders.store')",
                'button' => 'Заказать',
                'fields' => [
                    ['name' => 'address', 'type' => 'text', 'label' => 'Адрес доставки'],
                    ['name' => 'phone', 'type' => 'tel', 'label' => 'Телефон'],
                    ['name' => 'delivery_at', 'type' => 'datetime-local', 'label' => 'Дата и время'],
                    ['name' => 'pizza_id', 'type' => 'select', 'label' => 'Пицца', 'options' => ['Маргарита', 'Пепперони', 'Гавайская', 'Четыре сыра', 'Мясная', 'Барбекю']],
                    ['name' => 'custom', 'type' => 'checkbox', 'label' => 'Иная пицца'],
                    ['name' => 'custom_pizza', 'type' => 'text', 'label' => 'Какая пицца'],
                    ['name' => 'quantity', 'type' => 'number', 'label' => 'Количество'],
                    ['name' => 'payment_type', 'type' => 'select', 'label' => 'Оплата', 'options' => ['Наличные', 'Карта']],
                ],
            ],
        ];

        $drills = [];

        foreach ($forms as $form) {
            $controls = '';
            $checks = [
                ['type' => 'contains', 'label' => 'Тег form', 'value' => '<form'],
                ['type' => 'contains', 'label' => 'Метод POST', 'value' => 'method="POST"'],
                ['type' => 'contains', 'label' => 'CSRF-токен', 'value' => '@csrf'],
                ['type' => 'contains', 'label' => 'Подписи полей', 'value' => 'form-label'],
                ['type' => 'contains', 'label' => 'Класс контрола', 'value' => 'form-control'],
                ['type' => 'contains', 'label' => 'Класс ошибки', 'value' => 'is-invalid'],
                ['type' => 'contains', 'label' => 'Текст ошибки', 'value' => 'invalid-feedback'],
            ];

            foreach ($form['fields'] as $field) {
                $name = $field['name'];
                $label = $field['label'];
                $type = $field['type'];

                if ($type === 'select' || $type === 'textarea') {
                    $checks[] = ['type' => 'contains', 'label' => 'Поле «'.$label.'»', 'value' => 'name="'.$name.'"'];
                } else {
                    $checks[] = ['type' => 'contains', 'label' => 'Поле «'.$label.'»', 'value' => 'name="'.$name.'"'];
                }

                $checks[] = ['type' => 'contains', 'label' => 'Старое значение «'.$label.'»', 'value' => "old('".$name."')"];
                $checks[] = ['type' => 'contains', 'label' => 'Ошибка «'.$label.'»', 'value' => "@error('".$name."')"];

                if ($type === 'radio') {
                    $checks[] = ['type' => 'contains', 'label' => 'Радио-кнопки', 'value' => 'type="radio"'];
                    $radios = '';

                    foreach ($field['options'] as $value => $text) {
                        $checks[] = ['type' => 'contains', 'label' => 'Вариант «'.$text.'»', 'value' => 'value="'.$value.'"'];

                        $radios .= '        <div class="form-check">'."\n"
                            .'            <input type="radio" id="'.$name.'_'.$value.'" name="'.$name.'" value="'.$value.'" class="form-check-input @error(\''.$name.'\') is-invalid @enderror" @checked(old(\''.$name.'\') === \''.$value.'\')>'."\n"
                            .'            <label class="form-check-label" for="'.$name.'_'.$value.'">'.$text.'</label>'."\n"
                            .'        </div>'."\n";
                    }

                    $controls .= '    <fieldset class="mb-3">'."\n"
                        .'        <legend class="form-label">'.$label.'</legend>'."\n"
                        .$radios
                        .'        @error(\''.$name.'\')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror'."\n"
                        .'    </fieldset>'."\n";

                    continue;
                }

                $control = '';
                $id = ' id="'.$name.'"';

                if ($type === 'select') {
                    $options = '';
                    foreach ($field['options'] as $option) {
                        $options .= '            <option value="'.$option.'" @selected(old(\''.$name.'\') === \''.$option.'\')>'.$option.'</option>'."\n";
                    }

                    $control = '<select'.$id.' name="'.$name.'" class="form-select @error(\''.$name.'\') is-invalid @enderror">'."\n"
                        .'            <option value="">Выберите</option>'."\n"
                        .$options
                        .'        </select>';
                } elseif ($type === 'textarea') {
                    $control = '<textarea'.$id.' name="'.$name.'" rows="4" class="form-control @error(\''.$name.'\') is-invalid @enderror">{{ old(\''.$name.'\') }}</textarea>';
                } elseif ($type === 'checkbox') {
                    $control = '<input type="checkbox"'.$id.' name="'.$name.'" value="1" class="form-check-input" @checked(old(\''.$name.'\'))>';
                } else {
                    $control = '<input type="'.$type.'"'.$id.' name="'.$name.'" class="form-control @error(\''.$name.'\') is-invalid @enderror" value="{{ old(\''.$name.'\') }}">';
                }

                $controls .= '    <div class="mb-3">'."\n"
                    .'        <label for="'.$name.'" class="form-label">'.$label.'</label>'."\n"
                    .'        '.$control."\n"
                    .'        @error(\''.$name.'\')<div class="invalid-feedback">{{ $message }}</div>@enderror'."\n"
                    .'    </div>'."\n";
            }

            $solution = '<form method="POST" action="{{ '.$form['action'].' }}" novalidate>'."\n"
                .'    @csrf'."\n"
                .$controls
                .'    <button type="submit" class="btn btn-primary">'.$form['button'].'</button>'."\n"
                .'</form>';

            $drills[] = [
                'slug' => $form['slug'],
                'track' => 'bootstrap',
                'title' => $form['title'],
                'level' => 'средний',
                'minutes' => 18,
                'prompt' => 'Соберите форму «'.$form['title'].'» на Bootstrap 5 и Blade. У каждого поля — подпись, старое значение и вывод ошибки.',
                'requirements' => [
                    'Форма отправляется методом POST и содержит `@csrf`.',
                    'Поля размечены классами `form-label`, `form-control` (или `form-select`).',
                    'Ошибка поля — класс `is-invalid` и блок `invalid-feedback` внутри `@error`.',
                    'Значение сохраняется через `old(\'имя_поля\')`.',
                    'Кнопка отправки — `.btn.btn-primary`.',
                ],
                'starter_code' => '<form method="POST" action="{{ '.$form['action'].' }}">'."\n"
                    .'    @csrf'."\n"
                    .'    <!-- поля с подписями и ошибками -->'."\n"
                    .'</form>',
                'language' => 'blade',
                'checks' => ['checks' => $checks],
                'solution' => $solution,
            ];
        }

        return $drills;
    }

    private function php(): array
    {
        return [
            [
                'slug' => 'php-register-validation',
                'track' => 'php',
                'title' => 'Валидация регистрации на чистом PHP',
                'level' => 'средний',
                'minutes' => 20,
                'prompt' => 'Напишите обработчик регистрации: проверьте имя, email, пароль и повтор пароля. При ошибках вернитесь на форму, при успехе получите хеш пароля.',
                'requirements' => [
                    'Данные берутся из `$_POST`, пробелы обрезаются.',
                    'Email проверяется через `filter_var` и `FILTER_VALIDATE_EMAIL`.',
                    'Пароль — минимум 8 символов, повтор должен совпадать.',
                    'Ошибки складываются в массив `$errors`.',
                    'При ошибках — `header(\'Location: /register.php\')` и `exit`.',
                    'Пароль хешируется через `password_hash`.',
                ],
                'starter_code' => "<?php\n\n\$errors = [];\n\n// проверки\n\nif (\$errors) {\n    // вернуть на форму\n}\n\n// хеш пароля",
                'language' => 'php',
                'checks' => [
                    'php_lint' => true,
                    'checks' => [
                        ['type' => 'contains', 'label' => 'Данные из POST', 'value' => '$_POST'],
                        ['type' => 'contains', 'label' => 'Проверка email', 'value' => 'filter_var'],
                        ['type' => 'contains', 'label' => 'Фильтр email', 'value' => 'FILTER_VALIDATE_EMAIL'],
                        ['type' => 'contains', 'label' => 'Длина пароля', 'value' => 'mb_strlen'],
                        ['type' => 'contains', 'label' => 'Хеширование пароля', 'value' => 'password_hash'],
                        ['type' => 'contains', 'label' => 'Массив ошибок', 'value' => '$errors'],
                        ['type' => 'contains', 'label' => 'Возврат на форму', 'value' => "header('Location: /register.php'"],
                        ['type' => 'contains', 'label' => 'Поле имени', 'value' => "name"],
                        ['type' => 'contains', 'label' => 'Поле email', 'value' => "email"],
                        ['type' => 'contains', 'label' => 'Поле пароля', 'value' => "password"],
                        ['type' => 'contains', 'label' => 'Повтор пароля', 'value' => "password_confirmation"],
                    ],
                ],
                'solution' => <<<'PHP'
<?php
$errors = [];
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['password_confirmation'] ?? '';

if ($name === '' || mb_strlen($name) < 2) {
    $errors['name'] = 'Укажите имя.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Некорректный email.';
}

if (mb_strlen($password) < 8) {
    $errors['password'] = 'Минимум 8 символов.';
}

if ($password !== $confirm) {
    $errors['password_confirmation'] = 'Пароли не совпадают.';
}

if ($errors) {
    $_SESSION['errors'] = $errors;
    header('Location: /register.php');
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
PHP,
            ],
            [
                'slug' => 'php-application-validation',
                'track' => 'php',
                'title' => 'Валидация заявки и запись через PDO',
                'level' => 'средний',
                'minutes' => 22,
                'prompt' => 'Обработайте заявку: помещение, дата, оплата и телефон. Проверьте данные и запишите заявку в базу через подготовленный запрос.',
                'requirements' => [
                    '`room_id` приводится к целому через `filter_var` и `FILTER_VALIDATE_INT`.',
                    'Дата проверяется через `DateTime::createFromFormat`.',
                    'Способ оплаты — только `cash`, `card` или `sbp` через `in_array`.',
                    'Телефон — регулярка `+7` и 10 цифр.',
                    'Запись — `PDO`, `prepare` и `execute` с параметрами.',
                ],
                'starter_code' => "<?php\n\n\$errors = [];\n\n// проверки\n\nif (\$errors) {\n    // вернуть на форму\n}\n\n// INSERT через PDO",
                'language' => 'php',
                'checks' => [
                    'php_lint' => true,
                    'checks' => [
                        ['type' => 'contains', 'label' => 'Данные из POST', 'value' => '$_POST'],
                        ['type' => 'contains', 'label' => 'Целое число', 'value' => 'FILTER_VALIDATE_INT'],
                        ['type' => 'contains', 'label' => 'Проверка даты', 'value' => 'DateTime::createFromFormat'],
                        ['type' => 'contains', 'label' => 'Список оплат', 'value' => 'in_array'],
                        ['type' => 'contains', 'label' => 'Проверка телефона', 'value' => 'preg_match'],
                        ['type' => 'contains', 'label' => 'Подключение PDO', 'value' => 'new PDO'],
                        ['type' => 'contains', 'label' => 'Подготовленный запрос', 'value' => 'prepare'],
                        ['type' => 'contains', 'label' => 'Выполнение запроса', 'value' => 'execute'],
                        ['type' => 'contains', 'label' => 'Возврат на форму', 'value' => "header('Location: /application.php'"],
                        ['type' => 'contains', 'label' => 'Поле помещения', 'value' => 'room_id'],
                        ['type' => 'contains', 'label' => 'Поле даты', 'value' => 'start_date'],
                        ['type' => 'contains', 'label' => 'Поле оплаты', 'value' => 'payment_type'],
                    ],
                ],
                'solution' => <<<'PHP'
<?php
$errors = [];
$roomId = filter_var($_POST['room_id'] ?? null, FILTER_VALIDATE_INT);
$startDate = trim($_POST['start_date'] ?? '');
$payment = $_POST['payment_type'] ?? '';
$phone = trim($_POST['phone'] ?? '');

if (!$roomId) {
    $errors['room_id'] = 'Выберите помещение.';
}

$date = DateTime::createFromFormat('Y-m-d', $startDate);
if (!$date || $date->format('Y-m-d') !== $startDate) {
    $errors['start_date'] = 'Дата в формате ГГГГ-ММ-ДД.';
}

if (!in_array($payment, ['cash', 'card', 'sbp'], true)) {
    $errors['payment_type'] = 'Выберите способ оплаты.';
}

if (!preg_match('/^\+7\d{10}$/', $phone)) {
    $errors['phone'] = 'Телефон в формате +7XXXXXXXXXX.';
}

if ($errors) {
    $_SESSION['errors'] = $errors;
    header('Location: /application.php');
    exit;
}

$pdo = new PDO('sqlite:database.sqlite');
$stmt = $pdo->prepare('INSERT INTO applications (room_id, start_date, payment_type, phone) VALUES (?, ?, ?, ?)');
$stmt->execute([$roomId, $startDate, $payment, $phone]);
PHP,
            ],
            [
                'slug' => 'php-login-check',
                'track' => 'php',
                'title' => 'Вход с проверкой пароля',
                'level' => 'средний',
                'minutes' => 18,
                'prompt' => 'Сделайте обработчик входа: найдите пользователя по email и проверьте пароль через `password_verify`. При успехе сохраните `user_id` в сессии.',
                'requirements' => [
                    'Сессия стартует через `session_start()`.',
                    'Пользователь ищется подготовленным запросом `PDO`.',
                    'Пароль проверяется через `password_verify`.',
                    'При ошибке — сообщение в сессии и возврат на `/login.php`.',
                    'При успехе — `$_SESSION[\'user_id\']` и редирект на `/profile.php`.',
                ],
                'starter_code' => "<?php\n\nsession_start();\n\n// найти пользователя\n\n// проверить пароль\n",
                'language' => 'php',
                'checks' => [
                    'php_lint' => true,
                    'checks' => [
                        ['type' => 'contains', 'label' => 'Старт сессии', 'value' => 'session_start'],
                        ['type' => 'contains', 'label' => 'Проверка хеша', 'value' => 'password_verify'],
                        ['type' => 'contains', 'label' => 'Подключение PDO', 'value' => 'new PDO'],
                        ['type' => 'contains', 'label' => 'Подготовленный запрос', 'value' => 'prepare'],
                        ['type' => 'contains', 'label' => 'Выполнение запроса', 'value' => 'execute'],
                        ['type' => 'contains', 'label' => 'Сохранение пользователя', 'value' => '$_SESSION[\'user_id\']'],
                        ['type' => 'contains', 'label' => 'Редирект в кабинет', 'value' => "header('Location: /profile.php'"],
                        ['type' => 'contains', 'label' => 'Данные из POST', 'value' => '$_POST'],
                    ],
                ],
                'solution' => <<<'PHP'
<?php
session_start();
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$pdo = new PDO('sqlite:database.sqlite');
$stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['password_hash'])) {
    $_SESSION['error'] = 'Неверный email или пароль.';
    header('Location: /login.php');
    exit;
}

$_SESSION['user_id'] = $user['id'];
header('Location: /profile.php');
PHP,
            ],
            [
                'slug' => 'php-upload-check',
                'track' => 'php',
                'title' => 'Безопасная загрузка изображения',
                'level' => 'сложный',
                'minutes' => 25,
                'prompt' => 'Примите аватар: проверьте ошибку загрузки, размер до 2 МБ и реальный MIME-тип. Сохраните файл только если проверки прошли.',
                'requirements' => [
                    'Файл берётся из `$_FILES`.',
                    'Код ошибки сравнивается с `UPLOAD_ERR_OK`.',
                    'Размер ограничен 2 МБ.',
                    'MIME определяется через `finfo` и `FILEINFO_MIME_TYPE`.',
                    'Разрешены только `image/jpeg`, `image/png`, `image/webp`.',
                    'Сохранение — через `move_uploaded_file`, имя случайное.',
                ],
                'starter_code' => "<?php\n\n\$errors = [];\n\n// проверить файл\n\n// сохранить",
                'language' => 'php',
                'checks' => [
                    'php_lint' => true,
                    'checks' => [
                        ['type' => 'contains', 'label' => 'Массив файлов', 'value' => '$_FILES'],
                        ['type' => 'contains', 'label' => 'Код успешной загрузки', 'value' => 'UPLOAD_ERR_OK'],
                        ['type' => 'contains', 'label' => 'Проверка MIME', 'value' => 'finfo'],
                        ['type' => 'contains', 'label' => 'Тип MIME', 'value' => 'FILEINFO_MIME_TYPE'],
                        ['type' => 'contains', 'label' => 'Список типов', 'value' => 'in_array'],
                        ['type' => 'contains', 'label' => 'Сохранение файла', 'value' => 'move_uploaded_file'],
                        ['type' => 'contains', 'label' => 'Случайное имя', 'value' => 'random_bytes'],
                        ['type' => 'contains', 'label' => 'Ограничение размера', 'value' => "['size']"],
                    ],
                ],
                'solution' => <<<'PHP'
<?php
$errors = [];
$file = $_FILES['avatar'] ?? null;

if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    $errors['avatar'] = 'Выберите файл.';
} else {
    if ($file['size'] > 2 * 1024 * 1024) {
        $errors['avatar'] = 'Файл больше 2 МБ.';
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
        $errors['avatar'] = 'Только JPEG, PNG или WebP.';
    }
}

if (!$errors) {
    $name = bin2hex(random_bytes(8)) . '.jpg';
    move_uploaded_file($file['tmp_name'], __DIR__ . '/uploads/' . $name);
}
PHP,
            ],
            [
                'slug' => 'php-review-validation',
                'track' => 'php',
                'title' => 'Отзыв: оценка и обновление заявки',
                'level' => 'сложный',
                'minutes' => 22,
                'prompt' => 'Примите отзыв: оценка от 1 до 5, текст минимум 10 символов. Обновите заявку только текущего пользователя через PDO.',
                'requirements' => [
                    '`application_id` и `rating` проверяются через `filter_var` и `FILTER_VALIDATE_INT`.',
                    'Оценка в диапазоне 1–5.',
                    'Текст отзыва — минимум 10 символов.',
                    'Обновление — `UPDATE applications` через `prepare`.',
                    'В условии — `id` и `user_id` из сессии.',
                ],
                'starter_code' => "<?php\n\n\$errors = [];\n\n// проверки\n\n// UPDATE через PDO",
                'language' => 'php',
                'checks' => [
                    'php_lint' => true,
                    'checks' => [
                        ['type' => 'contains', 'label' => 'Целое число', 'value' => 'FILTER_VALIDATE_INT'],
                        ['type' => 'contains', 'label' => 'Длина текста', 'value' => 'mb_strlen'],
                        ['type' => 'contains', 'label' => 'Подключение PDO', 'value' => 'new PDO'],
                        ['type' => 'contains', 'label' => 'Подготовленный запрос', 'value' => 'prepare'],
                        ['type' => 'contains', 'label' => 'Обновление заявки', 'value' => 'UPDATE applications'],
                        ['type' => 'contains', 'label' => 'Поле оценки', 'value' => 'rating'],
                        ['type' => 'contains', 'label' => 'Поле отзыва', 'value' => 'review'],
                        ['type' => 'contains', 'label' => 'Текущий пользователь', 'value' => '$_SESSION[\'user_id\']'],
                    ],
                ],
                'solution' => <<<'PHP'
<?php
$errors = [];
$applicationId = filter_var($_POST['application_id'] ?? null, FILTER_VALIDATE_INT);
$rating = filter_var($_POST['rating'] ?? null, FILTER_VALIDATE_INT);
$text = trim($_POST['review'] ?? '');

if (!$applicationId) {
    $errors['application_id'] = 'Не выбрана заявка.';
}

if ($rating === false || $rating < 1 || $rating > 5) {
    $errors['rating'] = 'Оценка от 1 до 5.';
}

if (mb_strlen($text) < 10) {
    $errors['review'] = 'Отзыв минимум 10 символов.';
}

if ($errors) {
    $_SESSION['errors'] = $errors;
    header('Location: /review.php');
    exit;
}

$pdo = new PDO('sqlite:database.sqlite');
$stmt = $pdo->prepare('UPDATE applications SET rating = ?, review = ?, status = ? WHERE id = ? AND user_id = ?');
$stmt->execute([$rating, $text, 'completed', $applicationId, $_SESSION['user_id']]);
PHP,
            ],
        ];
    }

    private function sql(): array
    {
        return [
            [
                'slug' => 'sql-user-applications',
                'track' => 'sql',
                'title' => 'Заявки пользователя с названием помещения',
                'level' => 'средний',
                'minutes' => 15,
                'prompt' => 'Выведите заявки пользователя с `id = 1`: номер, название помещения, дату начала и статус. Отсортируйте по дате начала от новых к старым.',
                'requirements' => [
                    'Только `SELECT`.',
                    'Соедините `applications` и `rooms`.',
                    'Колонки: `id`, `room`, `start_date`, `status`.',
                    'Фильтр по `user_id = 1`.',
                    'Сортировка `start_date` по убыванию.',
                ],
                'starter_code' => 'select ...',
                'language' => 'sql',
                'checks' => [
                    'sql' => [
                        'fixture' => 'conference',
                        'label' => 'Результат запроса',
                        'columns' => ['id', 'room', 'start_date', 'status'],
                        'rows' => [
                            ['1', 'Коворкинг-зона «Вектор»', '2026-11-12', 'new'],
                            ['2', 'Кинозал «Пленум»', '2026-10-01', 'completed'],
                            ['5', 'Большая конференц-аудитория', '2026-09-15', 'new'],
                        ],
                        'ordered' => true,
                    ],
                ],
                'solution' => "select a.id, r.name as room, a.start_date, a.status\nfrom applications a\njoin rooms r on r.id = a.room_id\nwhere a.user_id = 1\norder by a.start_date desc",
            ],
            [
                'slug' => 'sql-status-counts',
                'track' => 'sql',
                'title' => 'Количество заявок по статусам',
                'level' => 'средний',
                'minutes' => 15,
                'prompt' => 'Посчитайте, сколько заявок в каждом статусе. Выведите статус и количество, отсортируйте по статусу по алфавиту.',
                'requirements' => [
                    'Только `SELECT`.',
                    'Группировка по `status`.',
                    'Колонки: `status`, `total`.',
                    'Сортировка по `status`.',
                ],
                'starter_code' => 'select ...',
                'language' => 'sql',
                'checks' => [
                    'sql' => [
                        'fixture' => 'conference',
                        'label' => 'Результат запроса',
                        'columns' => ['status', 'total'],
                        'rows' => [
                            ['assigned', '1'],
                            ['completed', '1'],
                            ['new', '3'],
                        ],
                        'ordered' => true,
                    ],
                ],
                'solution' => "select status, count(*) as total\nfrom applications\ngroup by status\norder by status",
            ],
            [
                'slug' => 'sql-new-applications',
                'track' => 'sql',
                'title' => 'Новые заявки с ФИО и помещением',
                'level' => 'средний',
                'minutes' => 18,
                'prompt' => 'Выведите новые заявки: ФИО пользователя, помещение и дату. Отсортируйте по дате начала.',
                'requirements' => [
                    'Только `SELECT`.',
                    'Соедините `applications`, `users` и `rooms`.',
                    'Колонки: `fio`, `room`, `start_date`.',
                    'Фильтр `status = \'new\'`.',
                    'Сортировка по `start_date`.',
                ],
                'starter_code' => 'select ...',
                'language' => 'sql',
                'checks' => [
                    'sql' => [
                        'fixture' => 'conference',
                        'label' => 'Результат запроса',
                        'columns' => ['fio', 'room', 'start_date'],
                        'rows' => [
                            ['Иванова Мария Сергеевна', 'Большая конференц-аудитория', '2026-09-15'],
                            ['Иванова Мария Сергеевна', 'Коворкинг-зона «Вектор»', '2026-11-12'],
                            ['Петров Пётр Петрович', 'Коворкинг-зона «Вектор»', '2026-11-20'],
                        ],
                        'ordered' => true,
                    ],
                ],
                'solution' => "select u.fio, r.name as room, a.start_date\nfrom applications a\njoin users u on u.id = a.user_id\njoin rooms r on r.id = a.room_id\nwhere a.status = 'new'\norder by a.start_date",
            ],
            [
                'slug' => 'sql-average-rating',
                'track' => 'sql',
                'title' => 'Средняя оценка завершённых заявок',
                'level' => 'легкий',
                'minutes' => 12,
                'prompt' => 'Посчитайте среднюю оценку среди завершённых заявок. Выведите с одним знаком после запятой, колонка — `avg_rating`.',
                'requirements' => [
                    'Только `SELECT`.',
                    'Фильтр `status = \'completed\'`.',
                    'Среднее через `avg(rating)`.',
                    'Формат с одним знаком через `printf(\'%.1f\', ...)`.',
                    'Колонка `avg_rating`.',
                ],
                'starter_code' => 'select ...',
                'language' => 'sql',
                'checks' => [
                    'sql' => [
                        'fixture' => 'conference',
                        'label' => 'Результат запроса',
                        'columns' => ['avg_rating'],
                        'rows' => [
                            ['5.0'],
                        ],
                        'ordered' => true,
                    ],
                ],
                'solution' => "select printf('%.1f', avg(rating)) as avg_rating\nfrom applications\nwhere status = 'completed'",
            ],
            [
                'slug' => 'sql-rooms-load',
                'track' => 'sql',
                'title' => 'Загрузка помещений от 90 мест',
                'level' => 'сложный',
                'minutes' => 20,
                'prompt' => 'Выведите помещения вместимостью от 90 мест и число заявок в каждом. Колонки: `name`, `capacity`, `total`. Сортировка по вместимости по убыванию.',
                'requirements' => [
                    'Только `SELECT`.',
                    '`LEFT JOIN` заявок к помещениям.',
                    'Фильтр `capacity >= 90`.',
                    'Группировка по помещению.',
                    'Колонки: `name`, `capacity`, `total`.',
                ],
                'starter_code' => 'select ...',
                'language' => 'sql',
                'checks' => [
                    'sql' => [
                        'fixture' => 'conference',
                        'label' => 'Результат запроса',
                        'columns' => ['name', 'capacity', 'total'],
                        'rows' => [
                            ['Большая конференц-аудитория', '120', '2'],
                            ['Кинозал «Пленум»', '90', '1'],
                        ],
                        'ordered' => true,
                    ],
                ],
                'solution' => "select r.name, r.capacity, count(a.id) as total\nfrom rooms r\nleft join applications a on a.room_id = r.id\nwhere r.capacity >= 90\ngroup by r.id\norder by r.capacity desc",
            ],
        ];
    }

    private function laravel(): array
    {
        return [
            [
                'slug' => 'laravel-migration-applications',
                'track' => 'laravel',
                'title' => 'Миграция заявок со связями',
                'level' => 'средний',
                'minutes' => 22,
                'prompt' => 'Напишите миграцию таблицы `applications`: пользователь, помещение, дата, оплата, статус, оценка и отзыв.',
                'requirements' => [
                    'Таблица `applications`.',
                    'Внешние ключи `user_id` и `room_id` через `foreignId()->constrained()`.',
                    'Дата `start_date`, строка `payment_type`.',
                    'Статус со значением по умолчанию `new`.',
                    'Оценка и отзыв — nullable.',
                    '`timestamps()` и метод `down()`.',
                ],
                'starter_code' => "<?php\n\nuse Illuminate\\Database\\Migrations\\Migration;\nuse Illuminate\\Database\\Schema\\Blueprint;\nuse Illuminate\\Support\\Facades\\Schema;\n\nreturn new class extends Migration\n{\n    public function up(): void\n    {\n        // Schema::create(...)\n    }\n};",
                'language' => 'php',
                'checks' => [
                    'php_lint' => true,
                    'checks' => [
                        ['type' => 'contains', 'label' => 'Создание таблицы', 'value' => "Schema::create('applications'"],
                        ['type' => 'contains', 'label' => 'Связь с пользователем', 'value' => "foreignId('user_id')"],
                        ['type' => 'contains', 'label' => 'Связь с помещением', 'value' => "foreignId('room_id')"],
                        ['type' => 'contains', 'label' => 'Внешний ключ', 'value' => '->constrained()'],
                        ['type' => 'contains', 'label' => 'Дата начала', 'value' => "date('start_date')"],
                        ['type' => 'contains', 'label' => 'Статус', 'value' => "string('status')"],
                        ['type' => 'contains', 'label' => 'Статус по умолчанию', 'value' => "->default('new')"],
                        ['type' => 'contains', 'label' => 'Отзыв', 'value' => "text('review')"],
                        ['type' => 'contains', 'label' => 'Метки времени', 'value' => 'timestamps()'],
                    ],
                ],
                'solution' => <<<'PHP'
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
            [
                'slug' => 'laravel-validation-registration',
                'track' => 'laravel',
                'title' => 'Правила валидации регистрации',
                'level' => 'средний',
                'minutes' => 18,
                'prompt' => 'Опишите правила валидации регистрации и заявки через `$request->validate`.',
                'requirements' => [
                    'Имя — обязательное, строка, до 255.',
                    'Email — обязательный, email, уникальный в `users`.',
                    'Пароль — минимум 8, с подтверждением.',
                    'Помещение — существует в `rooms`.',
                    'Дата — не раньше сегодняшней.',
                    'Оплата — только `cash`, `card`, `sbp`.',
                ],
                'starter_code' => "<?php\n\n\$data = \$request->validate([\n    // правила\n]);",
                'language' => 'php',
                'checks' => [
                    'php_lint' => true,
                    'checks' => [
                        ['type' => 'contains', 'label' => 'Вызов validate', 'value' => '$request->validate'],
                        ['type' => 'contains', 'label' => 'Правило имени', 'value' => "'name' =>"],
                        ['type' => 'contains', 'label' => 'Правило email', 'value' => "'email' =>"],
                        ['type' => 'contains', 'label' => 'Правило пароля', 'value' => "'password' =>"],
                        ['type' => 'contains', 'label' => 'Помещение', 'value' => "'room_id' =>"],
                        ['type' => 'contains', 'label' => 'Дата', 'value' => "'start_date' =>"],
                        ['type' => 'contains', 'label' => 'Оплата', 'value' => "'payment_type' =>"],
                        ['type' => 'contains', 'label' => 'Уникальность email', 'value' => 'unique:users,email'],
                        ['type' => 'contains', 'label' => 'Подтверждение пароля', 'value' => 'confirmed'],
                        ['type' => 'contains', 'label' => 'Минимум 8 символов', 'value' => 'min:8'],
                        ['type' => 'contains', 'label' => 'Существование помещения', 'value' => 'exists:rooms,id'],
                        ['type' => 'contains', 'label' => 'Список оплат', 'value' => 'in:cash,card,sbp'],
                    ],
                ],
                'solution' => <<<'PHP'
<?php

$data = $request->validate([
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'email', 'max:255', 'unique:users,email'],
    'password' => ['required', 'string', 'min:8', 'confirmed'],
    'room_id' => ['required', 'integer', 'exists:rooms,id'],
    'start_date' => ['required', 'date', 'after_or_equal:today'],
    'payment_type' => ['required', 'in:cash,card,sbp'],
]);
PHP,
            ],
            [
                'slug' => 'laravel-auth-attempt',
                'track' => 'laravel',
                'title' => 'Вход через Auth::attempt',
                'level' => 'средний',
                'minutes' => 18,
                'prompt' => 'Напишите метод входа: валидация, `Auth::attempt`, регенерация сессии и редирект в кабинет.',
                'requirements' => [
                    'Email и пароль валидируются.',
                    'Проверка учётных данных через `Auth::attempt`.',
                    'При ошибке — `withErrors` и `onlyInput`.',
                    'При успехе — `$request->session()->regenerate()`.',
                    'Редирект через `redirect()->intended`.',
                ],
                'starter_code' => "<?php\n\npublic function login(Request \$request)\n{\n    // валидация и вход\n}",
                'language' => 'php',
                'checks' => [
                    'php_lint' => true,
                    'checks' => [
                        ['type' => 'contains', 'label' => 'Валидация', 'value' => '$request->validate'],
                        ['type' => 'contains', 'label' => 'Проверка входа', 'value' => 'Auth::attempt'],
                        ['type' => 'contains', 'label' => 'Регенерация сессии', 'value' => '$request->session()->regenerate()'],
                        ['type' => 'contains', 'label' => 'Редирект', 'value' => 'redirect()->intended'],
                        ['type' => 'contains', 'label' => 'Ошибки', 'value' => 'withErrors'],
                        ['type' => 'contains', 'label' => 'Старое значение', 'value' => 'onlyInput'],
                    ],
                ],
                'solution' => <<<'PHP'
public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    if (! Auth::attempt($credentials)) {
        return back()->withErrors(['email' => 'Неверный email или пароль.'])->onlyInput('email');
    }

    $request->session()->regenerate();

    return redirect()->intended('/profile');
}
PHP,
            ],
            [
                'slug' => 'laravel-status-update',
                'track' => 'laravel',
                'title' => 'Смена статуса заявки администратором',
                'level' => 'средний',
                'minutes' => 16,
                'prompt' => 'Напишите метод смены статуса: валидация списка статусов и обновление заявки.',
                'requirements' => [
                    'Статус обязателен.',
                    'Допустимы `new`, `assigned`, `completed`, `rejected`.',
                    'Обновление через `$application->update`.',
                    'Возврат с сообщением `success`.',
                ],
                'starter_code' => "<?php\n\npublic function updateStatus(Request \$request, Application \$application)\n{\n    // валидация и обновление\n}",
                'language' => 'php',
                'checks' => [
                    'php_lint' => true,
                    'checks' => [
                        ['type' => 'contains', 'label' => 'Валидация', 'value' => '$request->validate'],
                        ['type' => 'contains', 'label' => 'Список статусов', 'value' => 'in:new,assigned,completed,rejected'],
                        ['type' => 'contains', 'label' => 'Обновление заявки', 'value' => '$application->update'],
                        ['type' => 'contains', 'label' => 'Поле статуса', 'value' => "'status'"],
                        ['type' => 'contains', 'label' => 'Сообщение', 'value' => "back()->with('success'"],
                    ],
                ],
                'solution' => <<<'PHP'
public function updateStatus(Request $request, Application $application)
{
    $data = $request->validate([
        'status' => ['required', 'in:new,assigned,completed,rejected'],
    ]);

    $application->update(['status' => $data['status']]);

    return back()->with('success', 'Статус обновлён.');
}
PHP,
            ],
            [
                'slug' => 'laravel-relations',
                'track' => 'laravel',
                'title' => 'Связи User, Application и Room',
                'level' => 'средний',
                'minutes' => 16,
                'prompt' => 'Опишите связи Eloquent: у пользователя много заявок, заявка принадлежит пользователю и помещению.',
                'requirements' => [
                    'В `User` — метод `applications()` с `hasMany`.',
                    'В `Application` — методы `user()` и `room()` с `belongsTo`.',
                    'Классы указаны через `::class`.',
                ],
                'starter_code' => "<?php\n\nclass User extends Model\n{\n    // applications\n}\n\nclass Application extends Model\n{\n    // user, room\n}",
                'language' => 'php',
                'checks' => [
                    'php_lint' => true,
                    'checks' => [
                        ['type' => 'contains', 'label' => 'Метод applications', 'value' => 'function applications'],
                        ['type' => 'contains', 'label' => 'Связь hasMany', 'value' => 'hasMany(Application::class)'],
                        ['type' => 'contains', 'label' => 'Метод user', 'value' => 'function user'],
                        ['type' => 'contains', 'label' => 'Связь с пользователем', 'value' => 'belongsTo(User::class)'],
                        ['type' => 'contains', 'label' => 'Метод room', 'value' => 'function room'],
                        ['type' => 'contains', 'label' => 'Связь с помещением', 'value' => 'belongsTo(Room::class)'],
                    ],
                ],
                'solution' => <<<'PHP'
<?php

class User extends Model
{
    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}

class Application extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
PHP,
            ],
        ];
    }
}
