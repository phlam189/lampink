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
        Schema::create('chapter_purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('chapter_id');
            $table->unsignedBigInteger('coin_transaction_id');
            $table->primary(['chapter_id', 'coin_transaction_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapter_purchases');
    }
};
