<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── View 1: Bestseller Stats by Category ──────────────────────────
        DB::unprepared('
            CREATE MATERIALIZED VIEW mv_bestseller_stats AS
            SELECT
                category_id,
                COUNT(*)                                            AS total_books,
                ROUND(AVG(price)::numeric, 2)                      AS avg_price,
                SUM(stock_quantity)                                AS total_inventory,
                COUNT(CASE WHEN stock_quantity > 500 THEN 1 END)  AS bestseller_count,
                MAX(published_at)                                  AS latest_publication
            FROM books
            WHERE is_active = true
            GROUP BY category_id;
        ');

        // ── View 2: Inventory Summary by Category ─────────────────────────
        DB::unprepared('
            CREATE MATERIALIZED VIEW mv_inventory_summary AS
            SELECT
                category_id,
                COUNT(*)                                                    AS total_books,
                SUM(stock_quantity)                                        AS total_stock,
                COUNT(CASE WHEN stock_quantity = 0 THEN 1 END)            AS out_of_stock,
                COUNT(CASE WHEN stock_quantity BETWEEN 1 AND 10 THEN 1 END) AS low_stock,
                COUNT(CASE WHEN stock_quantity > 10 THEN 1 END)           AS in_stock,
                ROUND(AVG(price)::numeric, 2)                              AS avg_price,
                MIN(price)                                                 AS min_price,
                MAX(price)                                                 AS max_price
            FROM books
            WHERE is_active = true
            GROUP BY category_id;
        ');

        // ── View 3: Format Distribution ───────────────────────────────────
        DB::unprepared('
            CREATE MATERIALIZED VIEW mv_format_distribution AS
            SELECT
                format,
                COUNT(*)                                            AS total_books,
                ROUND(AVG(price)::numeric, 2)                      AS avg_price,
                SUM(stock_quantity)                                AS total_stock,
                COUNT(CASE WHEN is_active = true THEN 1 END)      AS active_books
            FROM books
            GROUP BY format;
        ');

        // ── Regular indexes for fast lookups ──────────────────────────────
        DB::unprepared('CREATE INDEX idx_mv_bestseller_category  ON mv_bestseller_stats (category_id);');
        DB::unprepared('CREATE INDEX idx_mv_inventory_category   ON mv_inventory_summary (category_id);');
        DB::unprepared('CREATE INDEX idx_mv_format               ON mv_format_distribution (format);');

        // ── Unique indexes required for CONCURRENTLY refresh ──────────────
        DB::unprepared('CREATE UNIQUE INDEX idx_mv_bestseller_unique ON mv_bestseller_stats (category_id);');
        DB::unprepared('CREATE UNIQUE INDEX idx_mv_inventory_unique  ON mv_inventory_summary (category_id);');
        DB::unprepared('CREATE UNIQUE INDEX idx_mv_format_unique     ON mv_format_distribution (format);');
    }

    public function down(): void
    {
        DB::unprepared('DROP MATERIALIZED VIEW IF EXISTS mv_bestseller_stats;');
        DB::unprepared('DROP MATERIALIZED VIEW IF EXISTS mv_inventory_summary;');
        DB::unprepared('DROP MATERIALIZED VIEW IF EXISTS mv_format_distribution;');
    }
};