<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Module;
use App\Models\Recipe;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/course.json');

        if (! is_file($path)) {
            $this->command?->error('Не найден course.json. Запустите node tools/export-course.mjs');

            return;
        }

        $data = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($data) {
            foreach ($data['modules'] as $modulePosition => $moduleData) {
                $module = Module::updateOrCreate(
                    ['num' => $moduleData['num']],
                    [
                        'title' => $moduleData['title'],
                        'lead' => $moduleData['lead'],
                        'position' => $modulePosition,
                    ]
                );

                foreach ($moduleData['lessons'] as $lessonPosition => $lessonData) {
                    Lesson::updateOrCreate(
                        ['slug' => $lessonData['slug']],
                        [
                            'module_id' => $module->id,
                            'title' => $lessonData['title'],
                            'minutes' => $lessonData['minutes'],
                            'level' => $lessonData['level'],
                            'tags' => $lessonData['tags'],
                            'goal' => $lessonData['goal'],
                            'blocks' => $lessonData['blocks'],
                            'practice' => $lessonData['practice'],
                            'quiz' => $lessonData['quiz'],
                            'position' => $lessonPosition,
                            'is_published' => true,
                        ]
                    );
                }
            }

            foreach ($data['recipes'] as $position => $recipeData) {
                Recipe::updateOrCreate(
                    ['slug' => $recipeData['slug']],
                    [
                        'title' => $recipeData['title'],
                        'when_text' => $recipeData['when'],
                        'tags' => $recipeData['tags'],
                        'code' => $recipeData['code'],
                        'lang' => $recipeData['lang'],
                        'file' => $recipeData['file'],
                        'after_text' => $recipeData['after'],
                        'position' => $position,
                    ]
                );
            }
        });

        $this->command?->info(sprintf(
            'Курс загружен: %d модулей, %d уроков, %d рецептов.',
            Module::count(),
            Lesson::count(),
            Recipe::count()
        ));
    }
}
