<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('rating_avg', 3, 2)->default(0.00);
            $table->unsignedInteger('ratings_count')->default(0);
            $table->unsignedInteger('sales_count')->default(0);
            $table->unsignedInteger('purchases_count')->default(0);
            $table->decimal('response_rate', 5, 2)->default(0.00);
            $table->timestamp('member_since')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
