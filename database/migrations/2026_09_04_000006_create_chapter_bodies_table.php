<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chapter_bodies', function (Blueprint $table) {
            $table->foreignId('chapter_id')->primary()->constrained('chapters')->cascadeOnDelete();
            $table->longText('content')->nullable();
            $table->json('images')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapter_bodies');
    }
};
