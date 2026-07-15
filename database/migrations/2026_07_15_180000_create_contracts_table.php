<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_case_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->date('signed_at');
            $table->date('expires_at')->nullable();
            $table->string('status')->default('active');
            $table->string('original_document_path')->nullable();
            $table->string('original_document_name')->nullable();
            $table->string('original_document_mime_type')->nullable();
            $table->unsignedBigInteger('original_document_size')->nullable();
            $table->timestamps();

            $table->index(['status', 'expires_at']);
            $table->index(['legal_case_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
