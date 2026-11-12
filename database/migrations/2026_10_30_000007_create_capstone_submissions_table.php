<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capstone_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('project_path')->nullable();
            $table->string('repo_url')->nullable();
            $table->string('deploy_url')->nullable();
            $table->string('commit_hash', 100)->nullable();
            $table->unsignedTinyInteger('score')->default(0);
            $table->unsignedTinyInteger('max_score')->default(75);
            $table->json('results');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capstone_submissions');
    }
};
