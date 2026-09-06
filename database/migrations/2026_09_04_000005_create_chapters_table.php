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
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('novel_id')->constrained('novels')->cascadeOnDelete();
            $table->decimal('chapter_number', 8, 2);
            $table->string('title')->nullable();
            $table->unsignedInteger('price_coins')->default(0);
            $table->boolean('is_vip')->default(false);
            $table->foreignId('next_chapter_id')->nullable()->constrained('chapters')->nullOnDelete();
            $table->foreignId('prev_chapter_id')->nullable()->constrained('chapters')->nullOnDelete();
            $table->unsignedBigInteger('views_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['next_chapter_id', 'prev_chapter_id'], 'idx_next_prev');
            $table->index(['novel_id', 'chapter_number'], 'idx_novel_chapter');
            $table->index(['is_vip'], 'idx_is_vip');
            $table->index(['views_count'], 'idx_views_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
