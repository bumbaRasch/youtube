<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('request_id')->primary();
            $table->string('outcome');
            $table->integer('duration_ms')->nullable();
            $table->string('client_ip')->nullable();
            $table->text('input_url')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
