<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CourseSeeder::class,
            LabSeeder::class,
            DrillSeeder::class,
            ProjectVariantSeeder::class,
            WatchVariantSeeder::class,
            BuildStepSeeder::class,
            CaseStudySeeder::class,
            BookexchangeCaseSeeder::class,
            RestaurantCaseSeeder::class,
            PizzaCaseSeeder::class,
            WatchCaseSeeder::class,
            ChronicleSeeder::class,
        ]);
    }
}
