<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_variants', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('theme');
            $table->text('summary');
            $table->unsignedSmallInteger('minutes')->default(210);
            $table->string('level')->default('базовый');
            $table->json('entities');
            $table->json('statuses');
            $table->json('routes');
            $table->json('requirements');
            $table->string('admin_login');
            $table->string('admin_password');
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->string('variant_slug')->nullable()->after('commit_hash');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('variant_slug');
        });

        Schema::dropIfExists('project_variants');
    }
};
