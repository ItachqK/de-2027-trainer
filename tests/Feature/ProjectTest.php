<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_save_project_and_stages(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/project', [
            'repo_url' => 'https://github.com/student/conference',
            'deploy_url' => 'http://127.0.0.1:8000',
            'commit_hash' => 'a1b2c3d',
            'notes' => 'Добавить фильтр по статусу.',
            'stages' => ['database', 'auth', 'applications'],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $project = Project::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(3, $project->completedStages());
        $this->assertTrue($project->stages['database']);
        $this->assertFalse($project->stages['admin']);
    }

    public function test_project_rejects_invalid_url(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/project')->post('/project', [
            'repo_url' => 'not-a-url',
        ]);

        $response->assertRedirect('/project');
        $response->assertSessionHasErrors('repo_url');
        $this->assertDatabaseCount('projects', 0);
    }

    public function test_projects_are_isolated_between_users(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();

        Project::create([
            'user_id' => $first->id,
            'repo_url' => 'https://github.com/first/project',
            'stages' => ['database' => true],
        ]);

        $this->actingAs($second)->get('/project')->assertOk()->assertDontSee('github.com/first/project');
    }
}
