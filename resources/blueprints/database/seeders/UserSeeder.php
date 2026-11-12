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
                'fio' => 'Администратор портала',
                'login' => '@@ADMIN_LOGIN@@',
                'phone' => '8(343)200-18-14',
                'email' => 'admin@example.ru',
                'password' => '@@ADMIN_PASSWORD@@',
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
        ];

        foreach ($users as $user) {
            User::updateOrCreate(['login' => $user['login']], $user);
        }
    }
}
