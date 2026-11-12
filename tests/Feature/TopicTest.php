<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopicTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'project_name' => 'Обмен книгами',
            'purpose' => 'обмена книгами между читателями',
            'object_singular' => 'книга',
            'object_plural' => 'книги',
            'object_options' => 'роман, учебник, детская книга',
            'event_word' => 'встреча',
            'date_label' => 'дата встречи',
            'status_new' => 'Новая',
            'status_assigned' => 'Встреча назначена',
            'status_done' => 'Завершено',
            'pay_1' => 'при личной встрече',
            'pay_2' => 'переводом по СБП',
            'review_text' => 'отзыв о состоянии книги',
            'admin_login' => 'Books2027',
            'admin_password' => 'Demo77',
        ], $overrides);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/topic')->assertRedirect('/login');
    }

    public function test_page_shows_kim_defaults(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/topic')
            ->assertOk()
            ->assertSee('Своя тема по критериям КИМ')
            ->assertSee('Конференции.РФ')
            ->assertSee('75 + 25');
    }

    public function test_brief_uses_custom_topic(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/topic', $this->payload());

        $response->assertOk();
        $response->assertSee('Обмен книгами');
        $response->assertSee('книга');
        $response->assertSee('Books2027');
        $response->assertSee('Разработка подсистем безопасности информационной системы', false);
        $response->assertSee('16,00');
    }

    public function test_required_fields_are_validated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/topic')->post('/topic', [
            'project_name' => 'Без темы',
        ]);

        $response->assertRedirect('/topic');
        $response->assertSessionHasErrors(['purpose', 'object_singular', 'admin_login']);
    }

    public function test_download_returns_markdown_file(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/topic/download', $this->payload());

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
        $this->assertStringContainsString('Техническое задание: Обмен книгами', $response->getContent());
        $this->assertStringContainsString('## 4. Критерии оценивания', $response->getContent());
    }
}
