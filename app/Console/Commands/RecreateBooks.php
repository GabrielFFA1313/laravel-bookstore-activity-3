<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecreateBooks extends Command
{
    protected $signature   = 'books:recreate';
    protected $description = 'Recreate the books table after accidental drop';

    public function handle(): void
    {
        $this->info('Recreating books table...');

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

        // Clear failed migration records so they can re-run
        DB::table('migrations')
            ->where('migration', 'like', '%partition_books%')
            ->orWhere('migration', 'like', '%optimize_books%')
            ->delete();

        $this->info('✅ Books table recreated!');
        $this->info('Count: ' . DB::table('books')->count());
    }
}