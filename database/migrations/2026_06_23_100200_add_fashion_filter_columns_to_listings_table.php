<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->string('size', 32)->nullable()->after('price');
            $table->string('color', 64)->nullable()->after('size');
            $table->foreignId('brand_id')->nullable()->after('color')->constrained('brands')->nullOnDelete();
            $table->string('listing_mode', 24)->nullable()->after('brand_id');

            $table->index('size');
            $table->index('color');
            $table->index('listing_mode');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropIndex(['size']);
            $table->dropIndex(['color']);
            $table->dropIndex(['listing_mode']);
            $table->dropColumn(['size', 'color', 'brand_id', 'listing_mode']);
        });
    }
};
