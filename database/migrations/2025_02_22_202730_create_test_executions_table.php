<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('test_executions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('script_id')->constrained('test_scripts')->onDelete('cascade');
            $table->foreignUuid('initiator_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('environment_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('status_id')->constrained('execution_statuses')->onDelete('cascade');
            $table->text('s3_results_key')->nullable();
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_executions');
    }
};
