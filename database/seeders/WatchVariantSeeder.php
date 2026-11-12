<?php

namespace Database\Seeders;

use App\Models\ProjectVariant;
use Illuminate\Database\Seeder;

class WatchVariantSeeder extends Seeder
{
    public function run(): void
    {
        ProjectVariant::updateOrCreate(
            ['slug' => 'watch'],
            [
                'title' => 'Часовой салон «Точный ход»',
                'theme' => 'Продажа часов',
                'summary' => 'Салон часов: каталог из шести моделей, заказ с примеркой, три статуса, два способа оплаты и отзывы после выдачи.',
                'minutes' => 240,
                'level' => 'средний',
                'entities' => [
                    [
                        'name' => 'users',
                        'title' => 'Пользователи',
                        'fields' => [
                            ['name' => 'id', 'type' => 'PK', 'note' => ''],
                            ['name' => 'fio', 'type' => 'string', 'note' => 'ФИО, кириллица'],
                            ['name' => 'login', 'type' => 'string', 'note' => 'уникальный, латиница и цифры'],
                            ['name' => 'phone', 'type' => 'string', 'note' => '8(XXX)XXX-XX-XX'],
                            ['name' => 'email', 'type' => 'string', 'note' => 'уникальный'],
                            ['name' => 'password', 'type' => 'string', 'note' => 'хеш'],
                            ['name' => 'role', 'type' => 'string', 'note' => 'user / admin'],
                        ],
                    ],
                    [
                        'name' => 'watches',
                        'title' => 'Часы',
                        'fields' => [
                            ['name' => 'id', 'type' => 'PK', 'note' => ''],
                            ['name' => 'brand', 'type' => 'string', 'note' => 'бренд'],
                            ['name' => 'model', 'type' => 'string', 'note' => 'модель'],
                            ['name' => 'slug', 'type' => 'string', 'note' => 'уникальный'],
                            ['name' => 'price', 'type' => 'integer', 'note' => 'рубли'],
                            ['name' => 'case_mm', 'type' => 'integer', 'note' => 'диаметр корпуса'],
                            ['name' => 'movement', 'type' => 'string', 'note' => 'quartz / mechanical / automatic'],
                            ['name' => 'stock', 'type' => 'integer', 'note' => 'остаток'],
                            ['name' => 'dial', 'type' => 'string', 'note' => 'палитра циферблата'],
                            ['name' => 'summary', 'type' => 'string', 'note' => 'короткое описание'],
                            ['name' => 'description', 'type' => 'text', 'note' => 'полное описание'],
                        ],
                    ],
                    [
                        'name' => 'orders',
                        'title' => 'Заказы',
                        'fields' => [
                            ['name' => 'id', 'type' => 'PK', 'note' => ''],
                            ['name' => 'user_id', 'type' => 'FK users', 'note' => ''],
                            ['name' => 'watch_id', 'type' => 'FK watches', 'note' => ''],
                            ['name' => 'quantity', 'type' => 'integer', 'note' => '1-3'],
                            ['name' => 'total', 'type' => 'integer', 'note' => 'считает сервер'],
                            ['name' => 'pickup_date', 'type' => 'date', 'note' => 'не в прошлом'],
                            ['name' => 'payment', 'type' => 'string', 'note' => 'onsite / sbp'],
                            ['name' => 'status', 'type' => 'string', 'note' => 'new / ready / issued'],
                            ['name' => 'comment', 'type' => 'string', 'note' => 'nullable, до 500'],
                        ],
                    ],
                    [
                        'name' => 'reviews',
                        'title' => 'Отзывы',
                        'fields' => [
                            ['name' => 'id', 'type' => 'PK', 'note' => ''],
                            ['name' => 'order_id', 'type' => 'FK orders', 'note' => 'уникальный: один отзыв на заказ'],
                            ['name' => 'user_id', 'type' => 'FK users', 'note' => ''],
                            ['name' => 'rating', 'type' => 'integer', 'note' => '1-5'],
                            ['name' => 'text', 'type' => 'string', 'note' => '10-600 символов'],
                        ],
                    ],
                ],
                'statuses' => [
                    ['key' => 'new', 'label' => 'Новый', 'who' => 'система'],
                    ['key' => 'ready', 'label' => 'Готов к выдаче', 'who' => 'администратор'],
                    ['key' => 'issued', 'label' => 'Выдан', 'who' => 'администратор'],
                ],
                'routes' => [
                    ['method' => 'GET', 'uri' => '/', 'action' => 'каталог и отзывы'],
                    ['method' => 'GET|POST', 'uri' => '/register', 'action' => 'регистрация'],
                    ['method' => 'GET|POST', 'uri' => '/login', 'action' => 'вход'],
                    ['method' => 'POST', 'uri' => '/logout', 'action' => 'выход'],
                    ['method' => 'GET', 'uri' => '/orders', 'action' => 'мои заказы'],
                    ['method' => 'GET|POST', 'uri' => '/orders/create', 'action' => 'оформление заказа'],
                    ['method' => 'POST', 'uri' => '/orders/{order}/review', 'action' => 'отзыв по выданному заказу'],
                    ['method' => 'GET', 'uri' => '/admin/orders', 'action' => 'все заказы, фильтр и поиск'],
                    ['method' => 'PATCH', 'uri' => '/admin/orders/{order}/status', 'action' => 'смена статуса'],
                ],
                'requirements' => [
                    'Регистрация: ФИО, логин, телефон, email, пароль с подтверждением.',
                    'Валидация на сервере: формат телефона, уникальность логина и почты, сложность пароля.',
                    'Две роли: пользователь видит только свои заказы, администратор — все.',
                    'Каталог с ценой, характеристиками и остатком.',
                    'Заказ: модель, количество, дата получения, оплата, комментарий.',
                    'Три статуса с переходом только вперёд.',
                    'Два способа оплаты: при получении и переводом по СБП.',
                    'Отзыв только по выданному заказу и только один раз.',
                    'Адаптивность 390/768/1440 px, проверка консоли.',
                    'Git: осмысленные коммиты, README, отсутствие .env и vendor.',
                ],
                'admin_login' => 'TikTak2027',
                'admin_password' => 'Demo77',
                'position' => 10,
                'is_published' => true,
            ]
        );

        $this->command?->info('Тема «Часовой салон» добавлена.');
    }
}
