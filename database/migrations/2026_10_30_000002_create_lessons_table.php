<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->unsignedSmallInteger('minutes')->default(10);
            $table->string('level')->default('база');
            $table->json('tags');
            $table->text('goal');
            $table->json('blocks');
            $table->json('practice');
            $table->json('quiz');
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['module_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
