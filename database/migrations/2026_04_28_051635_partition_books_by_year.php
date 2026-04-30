<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Rename existing table to backup
        DB::unprepared('ALTER TABLE books RENAME TO books_old;');

        // Step 2: Create partitioned parent table
        // published_at NOT NULL — NULLs coerced to 1970-01-01 during INSERT
        DB::unprepared('
            CREATE TABLE books (
                id              BIGSERIAL,
                category_id     BIGINT,
                title           VARCHAR(255)    NOT NULL,
                author          VARCHAR(255)    NOT NULL,
                isbn            VARCHAR(255)    NOT NULL,
                price           DECIMAL(8,2)    NOT NULL,
                stock_quantity  INT             NOT NULL DEFAULT 0,
                description     TEXT,
                cover_image     VARCHAR(255),
                format          VARCHAR(255)    DEFAULT \'paperback\',
                is_active       BOOLEAN         DEFAULT TRUE,
                publisher       VARCHAR(255),
                published_at    DATE            NOT NULL DEFAULT \'1970-01-01\',
                created_at      TIMESTAMP,
                updated_at      TIMESTAMP,
                PRIMARY KEY     (id, published_at)
            ) PARTITION BY RANGE (published_at);
        ');

        // Step 3: Create partition child tables
        // Books with NULL published_at become 1970-01-01 → land in books_p_old
        DB::unprepared('
            CREATE TABLE books_p_old
                PARTITION OF books
                FOR VALUES FROM (MINVALUE) TO (\'2000-01-01\');
        ');
        DB::unprepared('
            CREATE TABLE books_p2000
                PARTITION OF books
                FOR VALUES FROM (\'2000-01-01\') TO (\'2005-01-01\');
        ');
        DB::unprepared('
            CREATE TABLE books_p2005
                PARTITION OF books
                FOR VALUES FROM (\'2005-01-01\') TO (\'2010-01-01\');
        ');
        DB::unprepared('
            CREATE TABLE books_p2010
                PARTITION OF books
                FOR VALUES FROM (\'2010-01-01\') TO (\'2015-01-01\');
        ');
        DB::unprepared('
            CREATE TABLE books_p2015
                PARTITION OF books
                FOR VALUES FROM (\'2015-01-01\') TO (\'2020-01-01\');
        ');
        DB::unprepared('
            CREATE TABLE books_p2020
                PARTITION OF books
                FOR VALUES FROM (\'2020-01-01\') TO (\'2025-01-01\');
        ');
        DB::unprepared('
            CREATE TABLE books_p_future
                PARTITION OF books
                FOR VALUES FROM (\'2025-01-01\') TO (MAXVALUE);
        ');

        // Step 4: Copy data — COALESCE converts NULL published_at to 1970-01-01
        // These records safely land in books_p_old partition
        DB::unprepared('
            INSERT INTO books (
                id, category_id, title, author, isbn, price,
                stock_quantity, description, cover_image, format,
                is_active, publisher, published_at, created_at, updated_at
            )
            SELECT
                id, category_id, title, author, isbn, price,
                stock_quantity, description, cover_image, format,
                is_active, publisher,
                COALESCE(published_at, \'1970-01-01\'),
                created_at, updated_at
            FROM books_old;
        ');

        // Step 5: Restore indexes
        DB::unprepared('CREATE INDEX IF NOT EXISTS idx_books_catalog_filter ON books (category_id, published_at, is_active);');
        DB::unprepared('CREATE INDEX IF NOT EXISTS idx_books_price_stock     ON books (price, stock_quantity, id);');
        DB::unprepared('CREATE INDEX IF NOT EXISTS idx_books_active          ON books (is_active);');
        DB::unprepared('CREATE INDEX IF NOT EXISTS idx_books_isbn_lookup     ON books (isbn);');
        // Step 6: Restore sequence
        DB::unprepared("SELECT setval('books_id_seq', (SELECT MAX(id) FROM books));");

        // Step 7: Drop old table
        DB::unprepared('DROP TABLE books_old;');
    }

    public function down(): void
    {
        DB::unprepared('ALTER TABLE books RENAME TO books_partitioned;');

        DB::unprepared('
            CREATE TABLE books (
                id              BIGSERIAL PRIMARY KEY,
                category_id     BIGINT,
                title           VARCHAR(255)    NOT NULL,
                author          VARCHAR(255)    NOT NULL,
                isbn            VARCHAR(255)    NOT NULL UNIQUE,
                price           DECIMAL(8,2)    NOT NULL,
                stock_quantity  INT             NOT NULL DEFAULT 0,
                description     TEXT,
                cover_image     VARCHAR(255),
                format          VARCHAR(255)    DEFAULT \'paperback\',
                is_active       BOOLEAN         DEFAULT TRUE,
                publisher       VARCHAR(255),
                published_at    DATE,
                created_at      TIMESTAMP,
                updated_at      TIMESTAMP
            );
        ');

        DB::unprepared('INSERT INTO books SELECT * FROM books_partitioned;');
        DB::unprepared("SELECT setval('books_id_seq', (SELECT MAX(id) FROM books));");
        DB::unprepared('DROP TABLE books_partitioned;');
    }
};