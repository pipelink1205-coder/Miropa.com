<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_category_migrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('old_category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('new_category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('status', 32)->default('mapped');
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->unique('listing_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_category_migrations');
    }
};
