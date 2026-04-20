<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── import_logs ───────────────────────────────────────────────────
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('file_name');
            $table->string('model_type'); // e.g. App\Models\Book
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->integer('total_rows')->default(0);
            $table->integer('success_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->json('failures')->nullable();
            $table->string('format')->nullable(); // csv, xlsx
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
        });

        // ── export_logs ───────────────────────────────────────────────────
        Schema::create('export_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('model_type');
            $table->string('format'); // csv, xlsx, pdf, json
            $table->json('filters')->nullable();
            $table->json('columns')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('completed');
            $table->string('file_name')->nullable();
            $table->string('download_link')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
        });

        // ── scheduled_tasks ───────────────────────────────────────────────
        Schema::create('scheduled_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('command');
            $table->string('description')->nullable();
            $table->enum('status', ['running', 'completed', 'failed'])->default('completed');
            $table->integer('duration_ms')->nullable(); // execution time in ms
            $table->text('output')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index('command');
            $table->index('status');
            $table->index('created_at');
        });

        // ── api_rate_limits ───────────────────────────────────────────────
        Schema::create('api_rate_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('ip_address', 45);
            $table->string('endpoint');
            $table->string('limiter'); // public, api, auth
            $table->integer('attempts');
            $table->integer('limit');
            $table->boolean('was_throttled')->default(false);
            $table->timestamp('window_start');
            $table->timestamps();

            $table->index('user_id');
            $table->index('ip_address');
            $table->index('was_throttled');
            $table->index('created_at');
        });

        // ── backup_monitoring ─────────────────────────────────────────────
        Schema::create('backup_monitoring', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['db', 'full', 'clean', 'monitor']);
            $table->enum('status', ['success', 'failed', 'running'])->default('running');
            $table->string('file_name')->nullable();
            $table->unsignedBigInteger('file_size_bytes')->nullable();
            $table->string('disk')->default('local_backups');
            $table->text('error_message')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_monitoring');
        Schema::dropIfExists('api_rate_limits');
        Schema::dropIfExists('scheduled_tasks');
        Schema::dropIfExists('export_logs');
        Schema::dropIfExists('import_logs');
    }
};