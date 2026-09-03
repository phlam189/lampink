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
        Schema::create('novels', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('author_id')->constrained('authors')->cascadeOnDelete();
            $table->foreignId('uploader_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('status')->default('ongoing'); // ongoing, completed, paused
            $table->string('type')->default('text'); // text, manga
            $table->decimal('revenue_share_rate', 5, 2)->default(70.00);
            $table->unsignedBigInteger('views_total')->default(0);
            $table->decimal('rating_avg', 3, 2)->default(0.00);
            $table->boolean('is_hot')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['views_total', 'is_hot'], 'idx_views_rank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('novels');
    }
};
