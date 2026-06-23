<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('universes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('accent_color', 16)->default('#c2410c');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('listing_universe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('universe_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['listing_id', 'universe_id']);
        });

        Schema::create('impact_factors', function (Blueprint $table) {
            $table->id();
            $table->string('product_type')->unique();
            $table->decimal('water_liters', 10, 2)->default(0);
            $table->decimal('co2_kg', 10, 3)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impact_factors');
        Schema::dropIfExists('listing_universe');
        Schema::dropIfExists('universes');
    }
};
