<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    // Step 1 — Add missing columns only if they don't exist
    Schema::table('books', function (Blueprint $table) {
        if (!Schema::hasColumn('books', 'format')) {
            $table->string('format')->default('paperback')->after('description');
        }
        if (!Schema::hasColumn('books', 'is_active')) {
            $table->boolean('is_active')->default(true)->after('format');
        }
        if (!Schema::hasColumn('books', 'publisher')) {
            $table->string('publisher')->nullable()->after('author');
        }
        if (!Schema::hasColumn('books', 'published_at')) {
            $table->date('published_at')->nullable()->after('is_active');
        }
    });

    // Step 2 — Add indexes safely
    Schema::table('books', function (Blueprint $table) {
        $table->index(
            ['category_id', 'published_at', 'is_active'],
            'idx_books_catalog_filter'
        );
        $table->index(
            ['price', 'stock_quantity', 'id'],
            'idx_books_price_stock'
        );
        $table->fullText(['title', 'description'], 'idx_books_fulltext');
        $table->index('is_active', 'idx_books_active');
        $table->index('isbn', 'idx_books_isbn_lookup');
    });
}
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex('idx_books_catalog_filter');
            $table->dropIndex('idx_books_price_stock');
            $table->dropFullText('idx_books_fulltext');
            $table->dropIndex('idx_books_active');
            $table->dropIndex('idx_books_isbn_lookup');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['format', 'is_active', 'publisher', 'published_at']);
        });
    }
};