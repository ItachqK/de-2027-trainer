<?php

namespace Tests\Feature;

use App\Models\Drill;
use App\Services\LabChecker;
use Database\Seeders\DrillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DrillSolutionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_drill_solution_passes_its_checks(): void
    {
        $this->seed(DrillSeeder::class);

        $checker = app(LabChecker::class);

        $this->assertSame(33, Drill::count());

        foreach (Drill::all() as $drill) {
            $result = $checker->checkRules($drill->checks ?? [], (string) $drill->solution);

            $failed = collect($result['results'])->reject(fn ($item) => $item['passed'])
                ->map(fn ($item) => $item['label'].': '.$item['message'])->implode(' | ');

            $this->assertTrue(
                $result['passed'],
                "Р’Р°СЂРёР°РЅС‚ {$drill->slug} РЅРµ РїСЂРѕС…РѕРґРёС‚ СЃРІРѕРё Р¶Рµ РїСЂРѕРІРµСЂРєРё. {$failed}"
            );
        }
    }

    public function test_every_drill_has_requirements_and_starter(): void
    {
        $this->seed(DrillSeeder::class);

        foreach (Drill::all() as $drill) {
            $this->assertNotEmpty($drill->requirements, $drill->slug);
            $this->assertNotEmpty($drill->starter_code, $drill->slug);
            $this->assertNotEmpty($drill->prompt, $drill->slug);
        }
    }
}
