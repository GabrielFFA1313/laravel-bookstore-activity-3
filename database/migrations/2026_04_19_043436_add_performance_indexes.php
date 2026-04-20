<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── BOOKS table indexes ───────────────────────────────────────────
        Schema::table('books', function (Blueprint $table) {
            // ISBN lookups (import/export, duplicate detection)
            if (!$this->indexExists('books', 'books_isbn_index')) {
                $table->index('isbn', 'books_isbn_index');
            }
            // Category filtering (browse by category)
            if (!$this->indexExists('books', 'books_category_id_index')) {
                $table->index('category_id', 'books_category_id_index');
            }
            // Price range filtering
            if (!$this->indexExists('books', 'books_price_index')) {
                $table->index('price', 'books_price_index');
            }
            // Stock availability checks
            if (!$this->indexExists('books', 'books_stock_quantity_index')) {
                $table->index('stock_quantity', 'books_stock_quantity_index');
            }
            // Full text search on title and author
            if (!$this->indexExists('books', 'books_title_author_index')) {
                $table->index(['title', 'author'], 'books_title_author_index');
            }
        });

        // ── ORDERS table indexes ──────────────────────────────────────────
        Schema::table('orders', function (Blueprint $table) {
            // Customer order history
            if (!$this->indexExists('orders', 'orders_user_id_index')) {
                $table->index('user_id', 'orders_user_id_index');
            }
            // Status filtering (admin order management)
            if (!$this->indexExists('orders', 'orders_status_index')) {
                $table->index('status', 'orders_status_index');
            }
            // Date range filtering for reports
            if (!$this->indexExists('orders', 'orders_created_at_index')) {
                $table->index('created_at', 'orders_created_at_index');
            }
            // Combined user + status for customer order filtering
            if (!$this->indexExists('orders', 'orders_user_id_status_index')) {
                $table->index(['user_id', 'status'], 'orders_user_id_status_index');
            }
        });

        // ── ORDER_ITEMS table indexes ─────────────────────────────────────
        Schema::table('order_items', function (Blueprint $table) {
            if (!$this->indexExists('order_items', 'order_items_order_id_index')) {
                $table->index('order_id', 'order_items_order_id_index');
            }
            if (!$this->indexExists('order_items', 'order_items_book_id_index')) {
                $table->index('book_id', 'order_items_book_id_index');
            }
        });

        // ── REVIEWS table indexes ─────────────────────────────────────────
        Schema::table('reviews', function (Blueprint $table) {
            if (!$this->indexExists('reviews', 'reviews_book_id_index')) {
                $table->index('book_id', 'reviews_book_id_index');
            }
            if (!$this->indexExists('reviews', 'reviews_user_id_index')) {
                $table->index('user_id', 'reviews_user_id_index');
            }
        });

        // ── USERS table indexes ───────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            if (!$this->indexExists('users', 'users_role_index')) {
                $table->index('role', 'users_role_index');
            }
            if (!$this->indexExists('users', 'users_email_verified_at_index')) {
                $table->index('email_verified_at', 'users_email_verified_at_index');
            }
        });

        // ── NOTIFICATIONS table indexes ───────────────────────────────────
        Schema::table('notifications', function (Blueprint $table) {
            if (!$this->indexExists('notifications', 'notifications_notifiable_read_at_index')) {
                $table->index(['notifiable_id', 'read_at'], 'notifications_notifiable_read_at_index');
            }
        });

        // ── AUDITS table indexes ──────────────────────────────────────────
        Schema::table('audits', function (Blueprint $table) {
            if (!$this->indexExists('audits', 'audits_event_index')) {
                $table->index('event', 'audits_event_index');
            }
            if (!$this->indexExists('audits', 'audits_created_at_index')) {
                $table->index('created_at', 'audits_created_at_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropIndexIfExists('books_isbn_index');
            $table->dropIndexIfExists('books_category_id_index');
            $table->dropIndexIfExists('books_price_index');
            $table->dropIndexIfExists('books_stock_quantity_index');
            $table->dropIndexIfExists('books_title_author_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndexIfExists('orders_user_id_index');
            $table->dropIndexIfExists('orders_status_index');
            $table->dropIndexIfExists('orders_created_at_index');
            $table->dropIndexIfExists('orders_user_id_status_index');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndexIfExists('order_items_order_id_index');
            $table->dropIndexIfExists('order_items_book_id_index');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndexIfExists('reviews_book_id_index');
            $table->dropIndexIfExists('reviews_user_id_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndexIfExists('users_role_index');
            $table->dropIndexIfExists('users_email_verified_at_index');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndexIfExists('notifications_notifiable_read_at_index');
        });

        Schema::table('audits', function (Blueprint $table) {
            $table->dropIndexIfExists('audits_event_index');
            $table->dropIndexIfExists('audits_created_at_index');
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        return collect(\Illuminate\Support\Facades\DB::select(
            "SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ?",
            [$table, $index]
        ))->isNotEmpty();
    }
};