<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trade_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('target_listing_id')->constrained('listings')->restrictOnDelete();
            $table->foreignId('offered_listing_id')->constrained('listings')->restrictOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'cancelled', 'completed'])->default('pending');
            $table->text('message')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['target_listing_id', 'status']);
            $table->index(['proposer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trade_offers');
    }
};
