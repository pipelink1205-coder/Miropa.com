<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->string('listing_type', 16)->default('individual')->after('listing_mode');
            $table->string('size_note', 32)->nullable()->after('size');
            $table->unsignedSmallInteger('items_count')->default(1)->after('size_note');
        });

        Schema::create('lot_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->string('size', 32)->nullable();
            $table->foreignId('condition_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lot_items');

        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['listing_type', 'size_note', 'items_count']);
        });
    }
};
