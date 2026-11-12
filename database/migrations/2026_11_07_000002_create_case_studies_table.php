<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_studies', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->foreignId('project_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('subtitle');
            $table->text('summary');
            $table->string('stack')->default('Laravel · SQLite · Blade');
            $table->unsignedSmallInteger('duration')->default(210);
            $table->string('level')->default('базовый');
            $table->json('chapters');
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_studies');
    }
};
