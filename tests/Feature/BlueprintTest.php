<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlueprintTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/blueprint')->assertRedirect('/login');
    }

    public function test_page_renders_with_kim_defaults(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/blueprint')
            ->assertOk()
            ->assertSee('Проект по теме: команды и код')
            ->assertSee('Конференции.РФ')
            ->assertSee('75 + 25');
    }

    public function test_topic_is_required(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/blueprint')
            ->post('/blueprint', ['topic' => ''])
            ->assertRedirect('/blueprint')
            ->assertSessionHasErrors('topic');
    }

    public function test_build_generates_project_for_custom_topic(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/blueprint', ['topic' => 'Обмен книгами'])
            ->assertRedirect(route('blueprint.result'));

        $this->actingAs($user)
            ->get('/blueprint/result')
            ->assertOk()
            ->assertSee('Обмен книгами')
            ->assertSee('книга')
            ->assertSee('Встреча назначена')
            ->assertSee('План на 4 часа')
            ->assertSee('Чек-лист 75 баллов')
            ->assertSee('app/Http/Controllers/BookingController.php');
    }

    public function test_preset_is_derived_from_topic(): void
    {
        $pizza = Blueprint::derive('Пицца на районе');
        $this->assertSame('пицца', $pizza['object_singular']);
        $this->assertSame('Доставлено', $pizza['status_done']);
        $this->assertStringContainsString('СБП', $pizza['pay_2']);

        $books = Blueprint::derive('Обмен книгами');
        $this->assertSame('книга', $books['object_singular']);
        $this->assertSame('ОК', $books['initials']);
    }

    public function test_unknown_topic_still_produces_valid_project(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/blueprint', ['topic' => 'Космический туризм'])
            ->assertRedirect(route('blueprint.result'));

        $this->actingAs($user)
            ->get('/blueprint/result')
            ->assertOk()
            ->assertSee('Космический туризм');

        $data = Blueprint::normalize(['topic' => 'Космический туризм']);

        $this->assertSame('Космический туризм', $data['project_name']);
        $this->assertNotSame('', $data['object_singular']);
        $this->assertNotSame('', $data['object_options']);

        $files = Blueprint::files($data);

        $this->assertArrayHasKey('routes/web.php', $files);
        $this->assertArrayHasKey('app/Models/Booking.php', $files);
        $this->assertStringContainsString('Космический туризм', $files['resources/views/layouts/app.blade.php']);
        $this->assertStringNotContainsString('@@PROJECT@@', $files['resources/views/layouts/app.blade.php']);
    }

    public function test_unknown_topic_falls_back_to_neutral_wording(): void
    {
        $data = Blueprint::normalize(['topic' => 'Свадьба']);

        $this->assertSame('услуга', $data['object_singular']);
        $this->assertSame('Заявка принята', $data['status_assigned']);
        $this->assertSame('приёма и обработки заявок клиентов', $data['purpose']);
        $this->assertStringNotContainsString('мероприят', mb_strtolower($data['purpose']));

        $speech = Blueprint::defense($data)['speech'];
        $this->assertStringContainsString('Свадьба', $speech);
        $this->assertStringNotContainsString('конференц', mb_strtolower($speech));
    }

    public function test_dictionary_covers_common_exam_topics(): void
    {
        $cases = [
            'Прокат самокатов' => 'позиция проката',
            'Детский лагерь' => 'путёвка',
            'Пункт выдачи заказов' => 'заказ',
            'Тюнинг' => 'автомобиль',
        ];

        foreach ($cases as $topic => $expected) {
            $this->assertSame($expected, Blueprint::normalize(['topic' => $topic])['object_singular'], $topic);
        }
    }

    public function test_result_redirects_without_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/blueprint/result')
            ->assertRedirect(route('blueprint.index'));
    }

    public function test_markdown_download_contains_commands_and_code(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/blueprint', ['topic' => 'Обмен книгами']);

        $response = $this->actingAs($user)->get('/blueprint/download');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
        $this->assertStringContainsString('Проект «Обмен книгами»', $response->getContent());
        $this->assertStringContainsString('composer create-project', $response->getContent());
        $this->assertStringContainsString('app/Models/Booking.php', $response->getContent());
        $this->assertStringContainsString('Чек-лист 75 баллов', $response->getContent());
    }

    public function test_zip_download_contains_project_files(): void
    {
        if (! class_exists(\ZipArchive::class)) {
            $this->markTestSkipped('Расширение zip не подключено.');
        }

        $user = User::factory()->create();

        $this->actingAs($user)->post('/blueprint', ['topic' => 'Пицца на районе']);

        $response = $this->actingAs($user)->get('/blueprint/project');

        $response->assertOk();
        $this->assertStringContainsString('.zip', (string) $response->headers->get('content-disposition'));

        $path = $response->baseResponse->getFile()->getPathname();
        $zip = new \ZipArchive();

        $this->assertTrue($zip->open($path) === true);
        $this->assertNotFalse($zip->locateName('routes/web.php'));
        $this->assertNotFalse($zip->locateName('public/css/app.css'));

        $readme = $zip->getFromName('README.md');
        $this->assertStringContainsString('Пицца на районе', $readme);

        $zip->close();
    }

    public function test_every_generated_file_is_shown_in_chapters(): void
    {
        $data = Blueprint::normalize(['topic' => 'Обмен книгами']);
        $files = array_keys(Blueprint::files($data));
        $build = Blueprint::build($data);

        $shown = [];

        foreach ($build['chapters'] as $chapter) {
            $shown = array_merge($shown, array_keys($chapter['code']));
        }

        $shown = array_values(array_unique($shown));
        sort($files);
        sort($shown);

        $this->assertSame($files, $shown, 'В инструкции должны быть все файлы проекта.');
        $this->assertSame(13, $build['stats']['tests']);
    }

    public function test_no_placeholders_remain_in_generated_files(): void
    {
        $data = Blueprint::normalize(['topic' => 'Обмен книгами']);

        foreach (Blueprint::files($data) as $path => $code) {
            $this->assertDoesNotMatchRegularExpression('/@@[A-Z_]+@@/', $code, 'Остался плейсхолдер в '.$path);
        }
    }

    public function test_generated_php_files_are_syntactically_valid(): void
    {
        $data = Blueprint::normalize(['topic' => 'Ремонт часов']);
        $files = Blueprint::files($data);

        foreach ($files as $path => $code) {
            if (! str_ends_with($path, '.php')) {
                continue;
            }

            $temp = tempnam(sys_get_temp_dir(), 'bp').'.php';
            file_put_contents($temp, $code);
            $output = [];
            exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($temp).' 2>&1', $output, $status);
            @unlink($temp);

            $this->assertSame(0, $status, 'Синтаксис сломан в '.$path.': '.implode(' ', $output));
        }
    }
}
