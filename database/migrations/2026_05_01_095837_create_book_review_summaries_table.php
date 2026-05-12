<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
{
    Schema::create('book_review_summaries', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('book_id')->unique();
        $table->text('summary');
        $table->enum('sentiment', ['positive', 'neutral', 'negative']);
        $table->decimal('sentiment_score', 4, 2)->default(0);
        $table->integer('reviews_analyzed')->default(0);
        $table->string('ai_provider')->default('gemini');
        $table->timestamp('last_analyzed_at')->nullable();
        $table->timestamps();

        // No foreign key constraint — just a unique index
        $table->index('book_id');
    });
}

    public function down(): void
    {
        Schema::dropIfExists('book_review_summaries');
    }
};