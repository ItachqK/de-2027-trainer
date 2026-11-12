<?php

namespace Database\Seeders;

use App\Models\Lab;
use App\Models\Module;
use Illuminate\Database\Seeder;

class LabSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/labs.json');

        if (! is_file($path)) {
            $this->command?->error('Не найден labs.json. Запустите node tools/build-labs.mjs');

            return;
        }

        $data = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        foreach ($data['labs'] as $position => $labData) {
            $module = Module::where('num', $labData['module_num'])->first();

            Lab::updateOrCreate(
                ['slug' => $labData['slug']],
                [
                    'module_id' => $module?->id,
                    'lesson_slug' => $labData['lesson_slug'] ?? null,
                    'title' => $labData['title'],
                    'level' => $labData['level'],
                    'minutes' => $labData['minutes'],
                    'goal' => $labData['goal'],
                    'brief' => $labData['brief'],
                    'steps' => $labData['steps'],
                    'starter_code' => $labData['starter_code'] ?? null,
                    'language' => $labData['language'],
                    'hints' => $labData['hints'] ?? [],
                    'solution' => $labData['solution'] ?? null,
                    'checks' => $labData['checks'],
                    'position' => $labData['position'] ?? $position,
                    'is_published' => true,
                ]
            );
        }

        $this->command?->info('Практикум загружен: '.Lab::count().' лабораторных.');
    }
}
