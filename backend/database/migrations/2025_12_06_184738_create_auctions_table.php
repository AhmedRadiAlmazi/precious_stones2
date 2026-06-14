<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->decimal('starting_price', 10, 2);
            $table->decimal('current_price', 10, 2);
            $table->decimal('reserve_price', 10, 2)->nullable();
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->enum('status', ['pending', 'active', 'ended', 'cancelled'])->default('pending');
            $table->foreignId('winner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('bid_increment', 10, 2)->default(100);
            $table->integer('total_bids')->default(0);
            $table->timestamps();
            
            // Indexes
            $table->index('product_id');
            $table->index('seller_id');
            $table->index('status');
            $table->index('end_time');
            $table->index('winner_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
