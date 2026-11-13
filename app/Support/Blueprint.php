<?php

namespace App\Support;

use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use FilesystemIterator;
use ZipArchive;

/**
 * Генератор учебного проекта под КИМ 09.02.07-3-2027.
 *
 * На входе — тема (например, «Обмен книгами»). На выходе:
 *  - готовые файлы Laravel-проекта (шаблоны из resources/blueprints);
 *  - пошаговая инструкция: команды, код, проверки, типичные ошибки;
 *  - план на 4 часа, чек-лист 75 баллов и речь для защиты.
 */
class Blueprint
{
    /** Поля, которые можно переопределить в форме. */
    public const FIELDS = [
        'project_name',
        'purpose',
        'object_singular',
        'object_plural',
        'object_options',
        'date_label',
        'status_new',
        'status_assigned',
        'status_done',
        'pay_1',
        'pay_2',
        'review_text',
        'admin_login',
        'admin_password',
    ];

    public static function defaults(): array
    {
        return [
            'topic' => 'Конференции.РФ',
            'project_name' => 'Конференции.РФ',
            'purpose' => 'бронирования помещений для проведения всероссийских конференций',
            'object_singular' => 'помещение',
            'object_plural' => 'помещения',
            'object_options' => 'аудитория, коворкинг, кинозал',
            'date_label' => 'дата начала конференции',
            'status_new' => 'Новая',
            'status_assigned' => 'Мероприятие назначено',
            'status_done' => 'Завершено',
            'pay_1' => 'при очном посещении',
            'pay_2' => 'переводом по системе СБП',
            'review_text' => 'отзыв о качестве проведённого мероприятия',
            'admin_login' => 'Admin2027',
            'admin_password' => 'Demo2027!',
            'initials' => 'КР',
            'slug' => 'konferencii-rf',
        ];
    }

    /**
     * Словарь тем. Ключи — подстроки в нижнем регистре.
     * Первое совпадение выигрывает, поэтому узкие темы стоят выше общих.
     */
    /** Словарь тем нужен и веб-версии генератора (docs/). */
    public static function dictionary(): array
    {
        return self::presets();
    }

    private static function presets(): array
    {
        return [
            ['keys' => ['конференц', 'мероприят', 'площадк', 'коворкинг'], 'data' => [
                'purpose' => 'бронирования помещений для проведения всероссийских конференций',
                'object_singular' => 'помещение',
                'object_plural' => 'помещения',
                'object_options' => 'аудитория, коворкинг, кинозал',
                'date_label' => 'дата начала конференции',
                'status_new' => 'Новая',
                'status_assigned' => 'Мероприятие назначено',
                'status_done' => 'Завершено',
                'pay_1' => 'при очном посещении',
                'pay_2' => 'переводом по системе СБП',
                'review_text' => 'отзыв о качестве проведённого мероприятия',
            ]],
            ['keys' => ['пицц', 'бургер', 'шаурм', 'суши', 'ролл', 'еда'], 'data' => [
                'purpose' => 'заказа пиццы и другой еды с доставкой на дом',
                'object_singular' => 'пицца',
                'object_plural' => 'пиццы',
                'object_options' => 'Маргарита, Пепперони, Четыре сыра',
                'date_label' => 'дата доставки',
                'status_new' => 'Новая',
                'status_assigned' => 'Заказ подтверждён',
                'status_done' => 'Доставлено',
                'pay_1' => 'при получении',
                'pay_2' => 'переводом по СБП',
                'review_text' => 'отзыв о качестве блюд и доставки',
            ]],
            ['keys' => ['книг', 'библиотек', 'букинист', 'читател'], 'data' => [
                'purpose' => 'обмена книгами между читателями',
                'object_singular' => 'книга',
                'object_plural' => 'книги',
                'object_options' => 'роман, учебник, детская книга',
                'date_label' => 'дата встречи',
                'status_new' => 'Новая',
                'status_assigned' => 'Встреча назначена',
                'status_done' => 'Завершено',
                'pay_1' => 'при личной встрече',
                'pay_2' => 'переводом по СБП',
                'review_text' => 'отзыв о состоянии книги',
            ]],
            ['keys' => ['час', 'ремонт час'], 'data' => [
                'purpose' => 'записи в мастерскую по ремонту часов',
                'object_singular' => 'часы',
                'object_plural' => 'часы',
                'object_options' => 'наручные, настенные, карманные',
                'date_label' => 'дата визита',
                'status_new' => 'Новая',
                'status_assigned' => 'Диагностика назначена',
                'status_done' => 'Готово',
                'pay_1' => 'при получении',
                'pay_2' => 'переводом по СБП',
                'review_text' => 'отзыв о качестве ремонта',
            ]],
            ['keys' => ['автосервис', 'ремонт авто', 'сто ', 'автомастер'], 'data' => [
                'purpose' => 'записи автомобилей в сервисный центр',
                'object_singular' => 'автомобиль',
                'object_plural' => 'автомобили',
                'object_options' => 'легковой, кроссовер, коммерческий',
                'date_label' => 'дата визита',
                'status_new' => 'Новая',
                'status_assigned' => 'Запись подтверждена',
                'status_done' => 'Работы завершены',
                'pay_1' => 'при получении',
                'pay_2' => 'переводом по СБП',
                'review_text' => 'отзыв о качестве ремонта',
            ]],
            ['keys' => ['стоматолог', 'клиник', 'медцентр', 'врач', 'поликлиник'], 'data' => [
                'purpose' => 'записи пациентов на приём в клинику',
                'object_singular' => 'услуга',
                'object_plural' => 'услуги',
                'object_options' => 'консультация, лечение, гигиена',
                'date_label' => 'дата приёма',
                'status_new' => 'Новая',
                'status_assigned' => 'Приём назначен',
                'status_done' => 'Завершено',
                'pay_1' => 'в клинике',
                'pay_2' => 'переводом по СБП',
                'review_text' => 'отзыв о качестве приёма',
            ]],
            ['keys' => ['фитнес', 'спортзал', 'тренировк', 'йог', 'бассейн'], 'data' => [
                'purpose' => 'записи на тренировки в фитнес-клуб',
                'object_singular' => 'тренировка',
                'object_plural' => 'тренировки',
                'object_options' => 'йога, силовая, растяжка',
                'date_label' => 'дата тренировки',
                'status_new' => 'Новая',
                'status_assigned' => 'Тренировка назначена',
                'status_done' => 'Завершено',
                'pay_1' => 'в зале',
                'pay_2' => 'переводом по СБП',
                'review_text' => 'отзыв о качестве тренировки',
            ]],
            ['keys' => ['кино', 'фильм', 'синема', 'театр'], 'data' => [
                'purpose' => 'бронирования мест в кинотеатре',
                'object_singular' => 'сеанс',
                'object_plural' => 'сеансы',
                'object_options' => 'утренний, дневной, вечерний',
                'date_label' => 'дата сеанса',
                'status_new' => 'Новая',
                'status_assigned' => 'Бронь подтверждена',
                'status_done' => 'Завершено',
                'pay_1' => 'в кассе',
                'pay_2' => 'переводом по СБП',
                'review_text' => 'отзыв о фильме и зале',
            ]],
            ['keys' => ['отел', 'гостиниц', 'хостел', 'номер'], 'data' => [
                'purpose' => 'бронирования номеров в гостинице',
                'object_singular' => 'номер',
                'object_plural' => 'номера',
                'object_options' => 'стандарт, комфорт, люкс',
                'date_label' => 'дата заезда',
                'status_new' => 'Новая',
                'status_assigned' => 'Бронь подтверждена',
                'status_done' => 'Завершено',
                'pay_1' => 'при заселении',
                'pay_2' => 'переводом по СБП',
                'review_text' => 'отзыв о проживании',
            ]],
            ['keys' => ['курс', 'обучен', 'школ', 'репетитор', 'урок'], 'data' => [
                'purpose' => 'записи на учебные курсы',
                'object_singular' => 'курс',
                'object_plural' => 'курсы',
                'object_options' => 'базовый, продвинутый, интенсив',
                'date_label' => 'дата начала курса',
                'status_new' => 'Новая',
                'status_assigned' => 'Запись подтверждена',
                'status_done' => 'Завершено',
                'pay_1' => 'при оплате в офисе',
                'pay_2' => 'переводом по СБП',
                'review_text' => 'отзыв о качестве обучения',
            ]],
            ['keys' => ['фото', 'съёмк', 'съемк'], 'data' => [
                'purpose' => 'бронирования времени фотосессии',
                'object_singular' => 'фотосессия',
                'object_plural' => 'фотосессии',
                'object_options' => 'портрет, семейная, предметная',
                'date_label' => 'дата съёмки',
                'status_new' => 'Новая',
                'status_assigned' => 'Съёмка назначена',
                'status_done' => 'Завершено',
                'pay_1' => 'при съёмке',
                'pay_2' => 'переводом по СБП',
                'review_text' => 'отзыв о качестве фотографий',
            ]],
            ['keys' => ['аренда авто', 'прокат авто', 'каршер', 'такси', 'трансфер'], 'data' => [
                'purpose' => 'аренды автомобилей и заказа поездок',
                'object_singular' => 'автомобиль',
                'object_plural' => 'автомобили',
                'object_options' => 'эконом, комфорт, бизнес',
                'date_label' => 'дата начала аренды',
                'status_new' => 'Новая',
                'status_assigned' => 'Бронь подтверждена',
                'status_done' => 'Завершено',
                'pay_1' => 'при получении',
                'pay_2' => 'переводом по СБП',
                'review_text' => 'отзыв о состоянии автомобиля',
            ]],
            ['keys' => ['ремонт квартир', 'отделк', 'строит', 'ремонт дом'], 'data' => [
                'purpose' => 'заявок на ремонт и отделку помещений',
                'object_singular' => 'объект',
                'object_plural' => 'объекты',
                'object_options' => 'квартира, дом, офис',
                'date_label' => 'дата замера',
                'status_new' => 'Новая',
                'status_assigned' => 'Замер назначен',
                'status_done' => 'Завершено',
                'pay_1' => 'при заключении договора',
                'pay_2' => 'переводом по СБП',
                'review_text' => 'отзыв о качестве ремонта',
            ]],
            ['keys' => ['животн', 'приют', 'ветеринар', 'зоо', 'питом'], 'data' => [
                'purpose' => 'записи животных в ветеринарную клинику',
                'object_singular' => 'питомец',
                'object_plural' => 'питомцы',
                'object_options' => 'кошка, собака, грызун',
                'date_label' => 'дата визита',
                'status_new' => 'Новая',
                'status_assigned' => 'Визит назначен',
                'status_done' => 'Завершено',
                'pay_1' => 'в клинике',
                'pay_2' => 'переводом по СБП',
                'review_text' => 'отзыв о качестве помощи',
            ]],
            ['keys' => ['квест', 'развлечен', 'боулинг', 'картинг', 'антик'], 'data' => [
                'purpose' => 'бронирования развлекательных программ',
                'object_singular' => 'игра',
                'object_plural' => 'игры',
                'object_options' => 'квест, боулинг, картинг',
                'date_label' => 'дата игры',
                'status_new' => 'Новая',
                'status_assigned' => 'Бронь подтверждена',
                'status_done' => 'Завершено',
                'pay_1' => 'на месте',
                'pay_2' => 'переводом по СБП',
                'review_text' => 'отзыв о проведённой игре',
            ]],
            ['keys' => ['салон', 'барбер', 'парикмахер', 'маникюр', 'красот'], 'data' => [
                'purpose' => 'записи клиентов в салон красоты',
                'object_singular' => 'услуга',
                'object_plural' => 'услуги',
                'object_options' => 'стрижка, окрашивание, маникюр',
                'date_label' => 'дата визита',
                'status_new' => 'Новая',
                'status_assigned' => 'Запись подтверждена',
                'status_done' => 'Завершено',
                'pay_1' => 'в салоне',
                'pay_2' => 'переводом по СБП',
                'review_text' => 'отзыв о качестве услуги',
            ]],
            ['keys' => ['цвет', 'букет', 'флор'], 'data' => [
                'purpose' => 'заказа и доставки цветов',
                'object_singular' => 'букет',
                'object_plural' => 'букеты',
                'object_options' => 'розы, тюльпаны, сборный',
                'date_label' => 'дата доставки',
                'status_new' => 'Новая',
                'status_assigned' => 'Заказ подтверждён',
                'status_done' => 'Доставлено',
                'pay_1' => 'при получении',
                'pay_2' => 'переводом по СБП',
                'review_text' => 'отзыв о качестве букета',
            ]],
            ['keys' => ['тур', 'экскурс', 'путешеств'], 'data' => [
                'purpose' => 'бронирования туров и экскурсий',
                'object_singular' => 'тур',
                'object_plural' => 'туры',
                'object_options' => 'городской, загородный, многодневный',
                'date_label' => 'дата поездки',
                'status_new' => 'Новая',
                'status_assigned' => 'Бронь подтверждена',
                'status_done' => 'Завершено',
                'pay_1' => 'в офисе',
                'pay_2' => 'переводом по СБП',
                'review_text' => 'отзыв о поездке',
            ]],
            ['keys' => ['груз', 'перевозк', 'доставк'], 'data' => [
                'purpose' => 'заказа грузоперевозок',
                'object_singular' => 'заказ',
                'object_plural' => 'заказы',
                'object_options' => 'квартира, офис, дача',
                'date_label' => 'дата перевозки',
                'status_new' => 'Новая',
                'status_assigned' => 'Перевозка назначена',
                'status_done' => 'Выполнено',
                'pay_1' => 'при получении',
                'pay_2' => 'переводом по СБП',
                'review_text' => 'отзыв о качестве перевозки',
            ]],
            ['keys' => ['недвижим', 'аренда жил', 'квартир'], 'data' => [
                'purpose' => 'подбора и аренды жилья',
                'object_singular' => 'квартира',
                'object_plural' => 'квартиры',
                'object_options' => 'студия, одно комнатная, двух комнатная',
                'date_label' => 'дата просмотра',
                'status_new' => 'Новая',
                'status_assigned' => 'Просмотр назначен',
                'status_done' => 'Завершено',
                'pay_1' => 'при подписании договора',
                'pay_2' => 'переводом по СБП',
                'review_text' => 'отзыв о качестве показа',
            ]],
            ['keys' => ['бар', 'кафе', 'ресторан', 'столик'], 'data' => [
                'purpose' => 'бронирования столиков в кафе',
                'object_singular' => 'столик',
                'object_plural' => 'столики',
                'object_options' => 'у окна, в зале, на веранде',
                'date_label' => 'дата брони',
                'status_new' => 'Новая',
                'status_assigned' => 'Бронь подтверждена',
                'status_done' => 'Завершено',
                'pay_1' => 'в заведении',
                'pay_2' => 'переводом по СБП',
                'review_text' => 'отзыв о качестве обслуживания',
            ]],
            ['keys' => ['ремонт техник', 'сервис техник', 'ноутбук', 'телефон', 'планшет'], 'data' => [
                'purpose' => 'записи на ремонт техники',
                'object_singular' => 'устройство',
                'object_plural' => 'устройства',
                'object_options' => 'ноутбук, смартфон, планшет',
                'date_label' => 'дата визита',
                'status_new' => 'Новая',
                'status_assigned' => 'Диагностика назначена',
                'status_done' => 'Готово',
                'pay_1' => 'при получении',
                'pay_2' => 'переводом по СБП',
                'review_text' => 'отзыв о качестве ремонта',
            ]],
            ['keys' => ['клининг', 'уборк', 'химчистк'], 'data' => [
                'purpose' => 'заказа клининговых услуг',
                'object_singular' => 'объект',
                'object_plural' => 'объекты',
                'object_options' => 'квартира, офис, коттедж',
                'date_label' => 'дата уборки',
                'status_new' => 'Новая',
                'status_assigned' => 'Уборка назначена',
                'status_done' => 'Выполнено',
                'pay_1' => 'при выполнении',
                'pay_2' => 'переводом по СБП',
                'review_text' => 'отзыв о качестве уборки',
            ]],
        ];
    }

    /**
     * Пустая форма: тема есть, детали не заданы — они подставятся по теме.
     */
    public static function blank(string $topic = ''): array
    {
        $data = ['topic' => $topic !== '' ? $topic : 'Конференции.РФ'];

        foreach (self::FIELDS as $field) {
            $data[$field] = '';
        }

        return $data;
    }

    public static function derive(string $topic): array
    {
        $topic = trim($topic);

        if ($topic === '') {
            return self::defaults();
        }

        $data = self::defaults();
        $haystack = mb_strtolower($topic);

        foreach (self::presets() as $preset) {
            foreach ($preset['keys'] as $needle) {
                if (str_contains($haystack, $needle)) {
                    $data = array_merge($data, $preset['data']);
                    break 2;
                }
            }
        }

        $data['topic'] = $topic;
        $data['project_name'] = $topic;
        $data['initials'] = self::initials($topic);
        $data['slug'] = Str::slug($topic) ?: 'project';

        return $data;
    }

    /** Убирает символы, которые могут сломать PHP, Blade или SQL. */
    public static function clean(?string $value, int $limit = 160): string
    {
        $value = (string) $value;
        $value = str_replace(['<', '>', '"', "'", '`', '\\', '&'], '', $value);
        $value = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $value) ?? '';
        $value = preg_replace('/\s+/u', ' ', $value) ?? '';

        return mb_substr(trim($value), 0, $limit);
    }

    public static function normalize(array $input): array
    {
        $topic = self::clean($input['topic'] ?? '', 80);
        $data = self::derive($topic);

        foreach (self::FIELDS as $field) {
            $value = self::clean($input[$field] ?? '', 160);

            if ($value !== '') {
                $data[$field] = $value;
            }
        }

        if ($data['project_name'] === '') {
            $data['project_name'] = $topic !== '' ? $topic : 'Мой проект';
        }

        $options = array_values(array_filter(array_map('trim', explode(',', $data['object_options']))));

        if (count($options) < 2) {
            $options = [$data['object_singular'], $data['object_plural']];
        }

        $data['object_options'] = implode(', ', array_slice($options, 0, 6));
        $data['initials'] = self::initials($data['project_name']);
        $data['slug'] = Str::slug($data['project_name']) ?: 'project';

        return $data;
    }

    public static function capitalize(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        return mb_strtoupper(mb_substr($value, 0, 1)).mb_substr($value, 1);
    }

    public static function initials(string $project): string
    {
        $parts = preg_split('/[^\p{L}\p{N}]+/u', $project, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $skip = ['на', 'в', 'и', 'по', 'для', 'с', 'у', 'о', 'от', 'до', 'the', 'of'];

        $letters = '';

        foreach ($parts as $part) {
            if (in_array(mb_strtolower($part), $skip, true)) {
                continue;
            }

            $letters .= mb_strtoupper(mb_substr($part, 0, 1));

            if (mb_strlen($letters) >= 2) {
                break;
            }
        }

        return $letters !== '' ? $letters : 'ДЭ';
    }

    public static function tokens(array $data): array
    {
        return [
            '@@PROJECT@@' => $data['project_name'],
            '@@PURPOSE@@' => $data['purpose'],
            '@@OBJECT@@' => $data['object_singular'],
            '@@OBJECT_PLURAL@@' => $data['object_plural'],
            '@@OPTIONS@@' => $data['object_options'],
            '@@DATE_LABEL@@' => $data['date_label'],
            '@@DATE_LABEL_UC@@' => self::capitalize($data['date_label']),
            '@@STATUS_NEW@@' => $data['status_new'],
            '@@STATUS_ASSIGNED@@' => $data['status_assigned'],
            '@@STATUS_DONE@@' => $data['status_done'],
            '@@PAY1@@' => $data['pay_1'],
            '@@PAY2@@' => $data['pay_2'],
            '@@REVIEW@@' => $data['review_text'],
            '@@ADMIN_LOGIN@@' => $data['admin_login'],
            '@@ADMIN_PASSWORD@@' => $data['admin_password'],
            '@@INITIALS@@' => $data['initials'],
            '@@SLUG@@' => $data['slug'],
        ];
    }

    /** @return array<string,string> путь относительно корня проекта => содержимое */
    public static function files(array $data): array
    {
        $root = resource_path('blueprints');

        if (! is_dir($root)) {
            throw new RuntimeException('Не найдена папка шаблонов resources/blueprints.');
        }

        $tokens = self::tokens($data);
        $files = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $path = str_replace('\\', '/', $file->getPathname());
            $relative = ltrim(substr($path, strlen(str_replace('\\', '/', $root))), '/');
            $files[$relative] = strtr((string) file_get_contents($file->getPathname()), $tokens);
        }

        ksort($files);

        return $files;
    }

    public static function build(array $data): array
    {
        $files = self::files($data);
        $chapters = self::chapters($data, $files);

        return [
            'data' => $data,
            'files' => $files,
            'chapters' => $chapters,
            'plan' => self::plan($chapters),
            'checklist' => self::checklist(),
            'defense' => self::defense($data),
            'stats' => [
                'files' => count($files),
                'chapters' => count($chapters),
                'minutes' => array_sum(array_column($chapters, 'minutes')),
                'tests' => 13,
                'lines' => array_sum(array_map(static fn (string $code): int => substr_count($code, "\n") + 1, $files)),
            ],
        ];
    }

    private static function chapter(
        int $n,
        string $title,
        int $minutes,
        string $goal,
        array $commands,
        array $paths,
        array $checks,
        array $mistakes,
        array $files
    ): array {
        $code = [];

        foreach ($paths as $path) {
            if (isset($files[$path])) {
                $code[$path] = $files[$path];
            }
        }

        return compact('n', 'title', 'minutes', 'goal', 'commands', 'paths', 'code', 'checks', 'mistakes');
    }

    public static function chapters(array $data, array $files): array
    {
        $slug = $data['slug'];

        return [
            self::chapter(1, 'Каркас проекта', 10,
                'Создать Laravel-проект, настроить .env, базу SQLite и запустить сервер. На этом шаге ещё нет вашего кода — проверяем, что окружение живое.',
                [
                    'php -v',
                    'composer -V',
                    'composer create-project laravel/laravel '.$slug,
                    'cd '.$slug,
                    'Copy-Item .env.example .env',
                    'php artisan key:generate',
                    'New-Item database\database.sqlite -ItemType File -Force',
                    'php artisan serve',
                ],
                ['.env.example'],
                [
                    'http://127.0.0.1:8000 открывается без ошибок.',
                    'В .env есть APP_KEY (не пустой).',
                    'Файл database/database.sqlite существует и не пустой после миграций.',
                ],
                [
                    'Забыли php artisan key:generate — Laravel падает с «No application encryption key».',
                    'Не создали database.sqlite — миграция пишет «Database file does not exist».',
                    'Запускают serve из чужой папки — artisan не находит bootstrap/app.php.',
                ],
                $files
            ),
            self::chapter(2, 'База данных: три таблицы', 25,
                'Создать users, bookings и reviews. Внешние ключи, enum для статусов и оплаты, unique на отзыв.',
                [
                    '# файлы миграций уже лежат в архиве — просто скопируйте их в database/migrations',
                    'php artisan migrate',
                    'php artisan migrate:status',
                    'php artisan db:show',
                ],
                [
                    'database/migrations/0001_01_01_000000_create_users_table.php',
                    'database/migrations/0001_01_01_000001_create_cache_table.php',
                    'database/migrations/0001_01_01_000002_create_jobs_table.php',
                    'database/migrations/2026_01_01_000001_create_bookings_table.php',
                    'database/migrations/2026_01_01_000002_create_reviews_table.php',
                ],
                [
                    'migrate:status показывает три миграции со статусом Ran.',
                    'В bookings есть внешний ключ user_id → users.id.',
                    'В reviews поле booking_id помечено unique — второй отзыв по заявке не пройдёт.',
                ],
                [
                    'Создали reviews раньше bookings — внешний ключ не создастся.',
                    'Забыли ->constrained() — в базе нет внешнего ключа, связи работают «на честном слове».',
                    'Поменяли enum после миграции без migrate:fresh — SQLite не применит изменение.',
                ],
                $files
            ),
            self::chapter(3, 'Модели и связи', 20,
                'Описать User, Booking и Review: fillable, casts, связи, статусы и правило перехода только вперёд.',
                [
                    'php artisan tinker --execute="dump(App\\Models\\Booking::STATUSES);"',
                    'php artisan tinker --execute="dump(App\\Models\\Booking::TRANSITIONS);"',
                ],
                [
                    'app/Models/User.php',
                    'app/Models/Booking.php',
                    'app/Models/Review.php',
                    'database/factories/UserFactory.php',
                ],
                [
                    'tinker печатает три статуса и карту переходов.',
                    '$booking->user и $booking->review возвращают связанные записи.',
                    '$user->bookings()->count() работает без SQL-ошибок.',
                ],
                [
                    'Назвали модель Request — конфликт с Illuminate\\Http\\Request. Поэтому модель называется Booking.',
                    'Забыли fillable — create() молча отбрасывает поля.',
                    'Хранят дату строкой: без cast «date» translatedFormat() падает.',
                ],
                $files
            ),
            self::chapter(4, 'Маршруты, роли и middleware', 20,
                'Связать страницы маршрутами, закрыть внутренние разделы auth, а админку — проверкой роли.',
                [
                    'php artisan route:list',
                    'php artisan route:list --path=admin',
                    'php artisan route:list --name=bookings',
                ],
                [
                    'routes/web.php',
                    'bootstrap/app.php',
                    'app/Http/Controllers/Controller.php',
                    'app/Http/Middleware/EnsureUserIsAdmin.php',
                ],
                [
                    'route:list показывает 9 маршрутов и имена home, login, register, bookings.*, admin.bookings.*.',
                    'Гость на /bookings получает редирект на /login.',
                    'Обычный пользователь на /admin/bookings получает 403.',
                ],
                [
                    'Не зарегистрировали alias admin в bootstrap/app.php — «Target class [admin] does not exist».',
                    'Поставили admin-маршруты вне группы auth — middleware роли не видит пользователя.',
                    'Дублировали имя маршрута — route() вернёт не тот URL.',
                ],
                $files
            ),
            self::chapter(5, 'Регистрация и вход', 30,
                'Сделать регистрацию с валидацией, вход по логину, выход и защиту от перебора паролей.',
                [
                    'php artisan route:list --name=login',
                    'php artisan route:list --name=register',
                ],
                [
                    'app/Http/Controllers/AuthController.php',
                    'resources/views/auth/register.blade.php',
                    'resources/views/auth/login.blade.php',
                ],
                [
                    'Регистрация с пустым полем показывает сообщение рядом с полем, а не 500.',
                    'Повторный логин и повторная почта отклоняются.',
                    'После входа открывается /bookings, после выхода — главная.',
                    'Пароль в базе — хеш, а не открытый текст.',
                ],
                [
                    'Сохранили пароль без хеша — потеря 16 баллов за безопасность.',
                    'Забыли session()->regenerate() — уязвимость фиксации сессии.',
                    'Валидация только в HTML (required) — её обходят отключением JS.',
                ],
                $files
            ),
            self::chapter(6, 'Заявки пользователя', 35,
                'Личный кабинет: список только своих заявок, создание заявки, пагинация, пустое состояние.',
                [
                    'php artisan migrate:fresh --seed',
                    'php artisan route:list --name=bookings',
                ],
                [
                    'app/Http/Controllers/BookingController.php',
                    'app/Providers/AppServiceProvider.php',
                    'resources/views/bookings/index.blade.php',
                    'resources/views/bookings/create.blade.php',
                    'resources/views/partials/status.blade.php',
                    'resources/views/pagination/custom.blade.php',
                ],
                [
                    'Новая заявка появляется в списке со статусом «'.$data['status_new'].'».',
                    'Дата в прошлом отклоняется с понятным сообщением.',
                    'Второй пользователь не видит чужие заявки.',
                    'При 20 заявках появляется пагинация, вёрстка не ломается.',
                ],
                [
                    'Отдают Booking::all() — пользователь видит чужие заявки. Только $request->user()->bookings().',
                    'Забыли withQueryString() — фильтры теряются при переходе по страницам.',
                    'Считают статусы в цикле — N+1 запросов, эксперт замечает по Debugbar.',
                ],
                $files
            ),
            self::chapter(7, 'Отзывы', 20,
                'Отзыв только после завершения заявки, только автору и только один раз. Отзывы выводятся на главной.',
                [
                    'php artisan route:list --name=reviews',
                ],
                [
                    'app/Http/Controllers/ReviewController.php',
                    'app/Models/Review.php',
                ],
                [
                    'На незавершённой заявке формы отзыва нет.',
                    'Повторная отправка даёт ошибку, а не вторую запись.',
                    'После публикации отзыв виден на главной странице.',
                ],
                [
                    'Проверяют только наличие статуса в шаблоне — прямой POST обходит запрет. Проверка нужна в контроллере.',
                    'Нет unique на booking_id — два отзыва при гонке запросов.',
                    'Не проверяют автора — можно оставить отзыв по чужой заявке.',
                ],
                $files
            ),
            self::chapter(8, 'Админка', 30,
                'Все заявки, фильтр по статусу, поиск по логину и названию, смена статуса только вперёд.',
                [
                    'php artisan route:list --path=admin',
                ],
                [
                    'app/Http/Controllers/Admin/BookingController.php',
                    'resources/views/admin/bookings/index.blade.php',
                ],
                [
                    'Админ видит все заявки, пользователь — только свои.',
                    'Фильтры «Все / '.$data['status_new'].' / '.$data['status_assigned'].' / '.$data['status_done'].'» считают записи.',
                    'Из «'.$data['status_new'].'» нельзя сразу перейти в «'.$data['status_done'].'».',
                    'После смены статуса сообщение об успехе одно, а не два.',
                ],
                [
                    'Разрешили любой статус из select — потеря логики и баллов.',
                    'Забыли withQueryString() — пагинация сбрасывает фильтр.',
                    'Считают counts четырьмя запросами на каждой странице — допустимо, но лучше один раз в контроллере.',
                ],
                $files
            ),
            self::chapter(9, 'Оформление и адаптив', 25,
                'Единая палитра «бумага и чернила», Georgia в заголовках, сургучный акцент. Проверка 390 / 768 / 1440 px.',
                [
                    '# откройте DevTools → Toggle device toolbar',
                    '# проверьте ширины 390, 768, 1440 — горизонтальной прокрутки быть не должно',
                ],
                [
                    'resources/views/layouts/app.blade.php',
                    'resources/views/home.blade.php',
                    'app/Http/Controllers/HomeController.php',
                    'public/css/app.css',
                    'public/js/app.js',
                    'public/favicon.svg',
                ],
                [
                    'На 390 px нет горизонтальной прокрутки, меню переносится.',
                    'У всех полей есть label, у ошибок — aria-invalid и role="alert".',
                    'Фокус с клавиатуры виден: Tab обходит ссылки и кнопки.',
                    'Контраст текста не ниже 4.5:1, кнопки не меньше 44 px по высоте.',
                ],
                [
                    'Фиксированная ширина 1200 px — на телефоне горизонтальный скролл.',
                    'Шрифт меньше 14 px в основной текст — минус за читаемость.',
                    'Эмодзи вместо иконок и «стеклянные» карточки — шаблонный вид, минус за дизайн.',
                ],
                $files
            ),
            self::chapter(10, 'Тестовые данные и тесты', 15,
                'Сиды с администратором и демо-заявками, 13 тестов на ключевые правила.',
                [
                    'php artisan migrate:fresh --seed',
                    'php artisan test',
                ],
                [
                    'database/seeders/DatabaseSeeder.php',
                    'database/seeders/UserSeeder.php',
                    'database/seeders/BookingSeeder.php',
                    'tests/Feature/AuthTest.php',
                    'tests/Feature/BookingTest.php',
                    'tests/Unit/ExampleTest.php',
                ],
                [
                    'php artisan test — все тесты зелёные.',
                    'Вход администратора '.$data['admin_login'].' / '.$data['admin_password'].' работает.',
                    'На главной есть два отзыва, в админке — заявки во всех трёх статусах.',
                ],
                [
                    'Сиды падают при повторном запуске — используйте updateOrCreate и проверку exists().',
                    'Тесты лезут в рабочую базу — нужен RefreshDatabase и sqlite :memory:.',
                    'Забыли пароль администратора в README — на защите не смогут войти.',
                ],
                $files
            ),
            self::chapter(11, 'Приёмка и защита', 10,
                'Пройти сценарий целиком, сверить чек-лист 75 баллов и отрепетировать защиту.',
                [
                    'php artisan test',
                    'php artisan route:list',
                    'php artisan serve',
                ],
                ['README.md'],
                [
                    'Сценарий: регистрация → заявка → админка → статус → отзыв — без единой ошибки.',
                    'README объясняет запуск с нуля за 5 команд.',
                    'Все пять страниц из ТЗ открываются и выглядят одинаково.',
                ],
                [
                    'Показывают код, но не работающий сценарий — эксперт оценивает результат.',
                    'Нет README и тестовых аккаунтов — минус за документацию.',
                    'Оставляют APP_DEBUG=true и .env в репозитории — минус за безопасность.',
                ],
                $files
            ),
        ];
    }

    public static function plan(array $chapters): array
    {
        $start = 9 * 60;
        $rows = [];

        foreach ($chapters as $chapter) {
            $rows[] = [
                'n' => $chapter['n'],
                'title' => $chapter['title'],
                'minutes' => $chapter['minutes'],
                'from' => self::clock($start),
                'to' => self::clock($start + $chapter['minutes']),
            ];

            $start += $chapter['minutes'];
        }

        return $rows;
    }

    private static function clock(int $minutes): string
    {
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    public static function checklist(): array
    {
        return [
            [
                'group' => 'Проектирование и разработка ИС — 25 баллов',
                'items' => [
                    ['6,00', 'Модули работают по ТЗ: 5 страниц, регистрация, заявки, статусы, отзывы.'],
                    ['16,00', 'Безопасность: хеши паролей, CSRF, middleware auth, проверка роли, валидация на сервере, экранирование вывода.'],
                    ['3,00', 'Технологии: Laravel, SQLite, README, ER-схема, коммиты.'],
                ],
            ],
            [
                'group' => 'Разработка дизайна — 25 баллов',
                'items' => [
                    ['11,00', 'Единая концепция: палитра, шрифты, отступы, состояния.'],
                    ['10,00', 'Современный вид: адаптив 390/768/1440, аккуратные формы и таблицы.'],
                    ['4,00', 'Требования к дизайну: целевая аудитория, сценарии, понятные подписи.'],
                ],
            ],
            [
                'group' => 'Интерфейс и приложение — 25 баллов',
                'items' => [
                    ['15,00', 'Интерфейс по ТЗ: все элементы на месте, ошибки рядом с полями, пустые состояния.'],
                    ['10,00', 'Приложение по ТЗ: данные сохраняются, статусы меняются, отзыв один раз.'],
                ],
            ],
        ];
    }

    public static function defense(array $data): array
    {
        return [
            'speech' => 'Здравствуйте. Мой проект — «'.$data['project_name'].'», информационная система для '.$data['purpose'].'. '
                .'Сделал её на Laravel с базой SQLite. В системе две роли: пользователь и администратор. '
                .'Пользователь регистрируется, выбирает «'.$data['object_singular'].'», указывает '.$data['date_label']
                .' и способ оплаты — '.$data['pay_1'].' или '.$data['pay_2'].'. '
                .'Заявка получает статус «'.$data['status_new'].'», администратор переводит её в «'.$data['status_assigned']
                .'» и затем в «'.$data['status_done'].'». После завершения пользователь один раз оставляет '
                .$data['review_text'].'. '
                .'Безопасность: пароли хранятся хешем, все формы с CSRF, внутренние страницы закрыты авторизацией, '
                .'админка — проверкой роли, данные валидируются на сервере. '
                .'Дизайн — «бумага и чернила»: тёплый фон, Georgia в заголовках, один сургучный акцент, '
                .'адаптив от 390 до 1440 пикселей. Проект покрыт 13 тестами, все проходят.',
            'demo' => [
                'Открыть главную: показать концепцию и блок отзывов.',
                'Зарегистрировать нового пользователя — показать валидацию (пустое поле, короткий пароль).',
                'Создать заявку: выбрать «'.$data['object_singular'].'», дату, оплату.',
                'Войти администратором '.$data['admin_login'].' и провести заявку по трём статусам.',
                'Вернуться пользователем и оставить отзыв; показать, что второй отзыв невозможен.',
                'Открыть DevTools: 390 px без горизонтальной прокрутки, консоль без ошибок.',
                'Показать терминал: php artisan test — всё зелёное.',
            ],
            'questions' => [
                ['Почему Laravel, а не чистый PHP?', 'Фреймворк даёт маршруты, ORM, CSRF, валидацию и шаблоны из коробки. Меньше кода — меньше ошибок, и это требование «современных технологий» в критериях.'],
                ['Как хранятся пароли?', 'Через cast hashed в модели User — bcrypt. Открытых паролей в базе нет, восстановление идёт через сброс, а не через чтение.'],
                ['Как защищены чужие заявки?', 'Список строится от $request->user()->bookings(), а отзыв проверяет user_id и статус done в контроллере, а не только в шаблоне.'],
                ['Почему статус нельзя вернуть назад?', 'В Booking::TRANSITIONS задана карта переходов, контроллер её проверяет. Это бизнес-правило из ТЗ, а не ограничение интерфейса.'],
                ['Что будет при 1000 заявок?', 'Пагинация по 8 и 12 записей, связи загружаются через with(), поэтому нет N+1.'],
                ['Как проверяли адаптив?', 'DevTools на 390, 768 и 1440 px, проверка горизонтальной прокрутки, фокуса с клавиатуры и контраста.'],
            ],
        ];
    }

    public static function markdown(array $data): string
    {
        $build = self::build($data);
        $d = $build['data'];

        $out = '# Проект «'.$d['project_name'].'» — инструкция от начала до конца'."\n\n";
        $out .= '> Тема: '.$d['project_name'].'. Критерии и структура — из КИМ 09.02.07-3-2027 (75 баллов).'."\n";
        $out .= '> Файлов: '.$build['stats']['files'].', глав: '.$build['stats']['chapters']
            .', времени: '.$build['stats']['minutes'].' минут, тестов: '.$build['stats']['tests']."\n\n";

        $out .= "## План на 4 часа\n\n";
        $out .= "| # | Этап | Время | Минуты |\n|---|---|---|---|\n";

        foreach ($build['plan'] as $row) {
            $out .= '| '.$row['n'].' | '.$row['title'].' | '.$row['from'].'–'.$row['to'].' | '.$row['minutes']." |\n";
        }

        $out .= "\n## Что получится\n\n";
        $out .= '- Проект: `'.$d['project_name'].'` (Laravel + SQLite + Blade).'."\n";
        $out .= '- Объект заявки: «'.$d['object_singular'].'» — '.$d['object_options'].'.'."\n";
        $out .= '- Статусы: «'.$d['status_new'].'» → «'.$d['status_assigned'].'» → «'.$d['status_done'].'».'."\n";
        $out .= '- Оплата: '.$d['pay_1'].' / '.$d['pay_2'].'.'."\n";
        $out .= '- Что оценивает отзыв: '.$d['review_text'].'.'."\n";
        $out .= '- Администратор: `'.$d['admin_login'].'` / `'.$d['admin_password'].'`.'."\n\n";

        foreach ($build['chapters'] as $chapter) {
            $out .= '## '.$chapter['n'].'. '.$chapter['title'].' — '.$chapter['minutes'].' мин'."\n\n";
            $out .= $chapter['goal']."\n\n";

            if ($chapter['commands']) {
                $out .= "### Команды\n\n```powershell\n".implode("\n", $chapter['commands'])."\n```\n\n";
            }

            if ($chapter['code']) {
                $out .= "### Код\n\n";

                foreach ($chapter['code'] as $path => $code) {
                    $out .= '#### `'.$path.'`'."\n\n";
                    $out .= "```".self::language($path)."\n".rtrim($code)."\n```\n\n";
                }
            }

            $out .= "### Проверка\n\n";

            foreach ($chapter['checks'] as $check) {
                $out .= '- [ ] '.$check."\n";
            }

            $out .= "\n### Типичные ошибки\n\n";

            foreach ($chapter['mistakes'] as $mistake) {
                $out .= '- '.$mistake."\n";
            }

            $out .= "\n";
        }

        $out .= "## Чек-лист 75 баллов\n\n";

        foreach ($build['checklist'] as $group) {
            $out .= '### '.$group['group']."\n\n";

            foreach ($group['items'] as $item) {
                $out .= '- [ ] **'.$item[0].'** — '.$item[1]."\n";
            }

            $out .= "\n";
        }

        $out .= "## Защита: что сказать\n\n".$build['defense']['speech']."\n\n";
        $out .= "### Демонстрация\n\n";

        foreach ($build['defense']['demo'] as $index => $step) {
            $out .= ($index + 1).'. '.$step."\n";
        }

        $out .= "\n### Вопросы и ответы\n\n";

        foreach ($build['defense']['questions'] as $pair) {
            $out .= '**'.$pair[0].'**'."\n\n".$pair[1]."\n\n";
        }

        $out .= "## Как развернуть архив\n\n";
        $out .= "1. Создайте проект: `composer create-project laravel/laravel ".$d['slug']."`.\n";
        $out .= "2. Распакуйте архив в папку проекта, подтвердив замену файлов.\n";
        $out .= "3. Выполните команды из главы 1, затем `php artisan migrate:fresh --seed`.\n";
        $out .= "4. Запустите `php artisan serve` и откройте http://127.0.0.1:8000.\n";

        return $out;
    }

    private static function language(string $path): string
    {
        return match (true) {
            str_ends_with($path, '.blade.php') => 'blade',
            str_ends_with($path, '.css') => 'css',
            str_ends_with($path, '.js') => 'javascript',
            str_ends_with($path, '.svg') => 'xml',
            str_ends_with($path, '.md') => 'markdown',
            str_ends_with($path, '.json') => 'json',
            str_ends_with($path, '.env.example') => 'ini',
            default => 'php',
        };
    }

    public static function zip(array $data): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Расширение PHP zip не подключено — скачайте .md и создайте файлы вручную.');
        }

        $path = rtrim(sys_get_temp_dir(), '\\/').DIRECTORY_SEPARATOR.'bp-'.Str::random(8).'.zip';
        $zip = new ZipArchive();

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Не удалось создать архив.');
        }

        foreach (self::files($data) as $name => $content) {
            $zip->addFromString($name, $content);
        }

        $zip->close();

        return $path;
    }

    public static function fileName(array $data, string $extension): string
    {
        return 'project-'.$data['slug'].'.'.$extension;
    }
}
