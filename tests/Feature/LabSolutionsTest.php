<?php

namespace Tests\Feature;

use App\Models\Lab;
use App\Services\LabChecker;
use Database\Seeders\CourseSeeder;
use Database\Seeders\LabSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabSolutionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_lab_solution_passes_its_checks(): void
    {
        $this->seed(CourseSeeder::class);
        $this->seed(LabSeeder::class);

        $checker = app(LabChecker::class);

        $this->assertSame(14, Lab::count());

        foreach (Lab::all() as $lab) {
            $result = $checker->check($lab, (string) $lab->solution);

            $failed = collect($result['results'])->reject(fn ($r) => $r['passed'])
                ->map(fn ($r) => $r['label'].': '.$r['message'])->implode(' | ');

            $this->assertTrue(
                $result['passed'],
                "Лабораторная {$lab->slug} не проходит проверку. {$failed}"
            );
        }
    }
}
