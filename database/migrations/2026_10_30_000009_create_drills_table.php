<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drills', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('track')->index();
            $table->string('title');
            $table->string('level')->default('средний');
            $table->unsignedSmallInteger('minutes')->default(15);
            $table->text('prompt');
            $table->json('requirements');
            $table->longText('starter_code')->nullable();
            $table->string('language')->default('html');
            $table->json('checks');
            $table->longText('solution');
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('drill_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('drill_id')->constrained()->cascadeOnDelete();
            $table->longText('code');
            $table->boolean('passed')->default(false);
            $table->json('results')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        Schema::create('drill_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('drill_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->unsignedSmallInteger('passes')->default(0);
            $table->unsignedSmallInteger('streak')->default(0);
            $table->longText('last_code')->nullable();
            $table->timestamp('last_passed_at')->nullable();
            $table->timestamp('next_review_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'drill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drill_progress');
        Schema::dropIfExists('drill_attempts');
        Schema::dropIfExists('drills');
    }
};
