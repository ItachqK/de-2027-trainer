<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('capstone_submissions', function (Blueprint $table) {
            $table->json('runtime')->nullable()->after('results');
            $table->boolean('runtime_passed')->default(false)->after('runtime');
        });
    }

    public function down(): void
    {
        Schema::table('capstone_submissions', function (Blueprint $table) {
            $table->dropColumn(['runtime', 'runtime_passed']);
        });
    }
};
