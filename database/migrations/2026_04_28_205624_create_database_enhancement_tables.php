<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Table 1: search_index_queue ───────────────────────────────────
        // Tracks pending Scout indexing jobs for the database queue driver.
        // When SCOUT_QUEUE=true, indexing is deferred to this queue.
        Schema::create('search_index_queue', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');           // e.g. App\Models\Book
            $table->unsignedBigInteger('model_id'); // the record to index
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
            ])->default('pending');
            $table->text('payload')->nullable();    // serialized model data
            $table->integer('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Indexes for queue processing efficiency
            $table->index(['status', 'scheduled_at'], 'idx_search_queue_status');
            $table->index(['model_type', 'model_id'], 'idx_search_queue_model');
        });

        // ── Table 2: query_performance_logs ───────────────────────────────
        // Stores slow query logs for ongoing performance monitoring.
        // Populated by middleware or DB::listen() in production.
        Schema::create('query_performance_logs', function (Blueprint $table) {
            $table->id();
            $table->text('query');                          // Full SQL query
            $table->json('bindings')->nullable();           // Query parameters
            $table->decimal('execution_time_ms', 10, 3);   // Milliseconds
            $table->string('connection')->default('pgsql'); // DB connection used
            $table->string('endpoint')->nullable();         // API route that triggered it
            $table->string('request_method', 10)->nullable();
            $table->string('user_id')->nullable();          // Who triggered it
            $table->string('ip_address', 45)->nullable();
            $table->boolean('is_slow')->default(false);     // true if > threshold
            $table->integer('threshold_ms')->default(100);  // Slow query threshold
            $table->timestamps();

            // Indexes for filtering and analysis
            $table->index('execution_time_ms', 'idx_qpl_execution_time');
            $table->index('is_slow',           'idx_qpl_is_slow');
            $table->index('created_at',        'idx_qpl_created_at');
            $table->index('endpoint',          'idx_qpl_endpoint');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('query_performance_logs');
        Schema::dropIfExists('search_index_queue');
    }
};