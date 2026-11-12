<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('build_steps', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('goal');
            $table->unsignedSmallInteger('minutes')->default(20);
            $table->json('commands');
            $table->json('files');
            $table->json('snippets');
            $table->json('checks');
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('build_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('build_step_id')->constrained()->cascadeOnDelete();
            $table->timestamp('completed_at');
            $table->timestamps();

            $table->unique(['user_id', 'build_step_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('build_progress');
        Schema::dropIfExists('build_steps');
    }
};
