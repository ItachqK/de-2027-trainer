<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Заглушка Laravel проверяет приветственную страницу, которой в проекте уже нет,
 * и падает без RefreshDatabase. Заменяем её на честную проверку главной.
 */
class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders(): void
    {
        $this->get('/')->assertOk()->assertSee('Что можно выбрать');
    }
}
