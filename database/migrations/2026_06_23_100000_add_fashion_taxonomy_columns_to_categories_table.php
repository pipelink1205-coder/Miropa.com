<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('level', 20)->nullable()->after('slug');
            $table->string('vertical', 20)->nullable()->after('level');
            $table->string('image')->nullable()->after('icon');

            $table->index(['vertical', 'level', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['vertical', 'level', 'is_active']);
            $table->dropColumn(['level', 'vertical', 'image']);
        });
    }
};
