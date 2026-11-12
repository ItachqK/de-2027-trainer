<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->nullable()->constrained()->nullOnDelete();
            $table->string('lesson_slug')->nullable()->index();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('level')->default('практика');
            $table->unsignedSmallInteger('minutes')->default(20);
            $table->text('goal');
            $table->longText('brief');
            $table->json('steps');
            $table->longText('starter_code')->nullable();
            $table->string('language')->default('php');
            $table->json('hints')->nullable();
            $table->longText('solution')->nullable();
            $table->json('checks');
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('lab_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lab_id')->constrained()->cascadeOnDelete();
            $table->longText('code')->nullable();
            $table->enum('status', ['draft', 'failed', 'passed'])->default('draft');
            $table->json('results')->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'lab_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_submissions');
        Schema::dropIfExists('labs');
    }
};
