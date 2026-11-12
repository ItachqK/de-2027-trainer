<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['login' => 'admin'],
            [
                'fio' => 'Администратор курса',
                'email' => 'admin@demo2027.local',
                'password' => Hash::make('Admin2027!'),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['login' => 'student'],
            [
                'fio' => 'Иванова Мария Сергеевна',
                'email' => 'student@demo2027.local',
                'password' => Hash::make('Student2027!'),
                'role' => 'student',
            ]
        );
    }
}
