<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        $faker = fake('ru_RU');

        return [
            'fio' => $faker->lastName().' '.$faker->firstName().' '.$faker->middleName(),
            'login' => 'user'.$faker->unique()->numerify('####'),
            'phone' => '8('.$faker->numerify('###').')'.$faker->numerify('###').'-'.$faker->numerify('##').'-'.$faker->numerify('##'),
            'email' => $faker->unique()->safeEmail(),
            'password' => 'Student2027!',
            'role' => 'user',
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => 'admin']);
    }
}
