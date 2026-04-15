<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_export_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['import', 'export']);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->string('file_name');
            $table->string('format')->nullable(); // csv, xlsx, pdf
            $table->integer('total_rows')->nullable();
            $table->integer('success_rows')->nullable();
            $table->integer('failed_rows')->nullable();
            $table->json('failures')->nullable(); // array of row errors
            $table->json('filters')->nullable();  // export filters used
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_export_logs');
    }
};