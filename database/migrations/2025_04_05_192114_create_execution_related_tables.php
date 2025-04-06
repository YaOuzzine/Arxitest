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
        Schema::create('execution_statuses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamp('created_at');
        });

        Schema::create('test_executions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('script_id');
            $table->uuid('initiator_id');
            $table->uuid('environment_id')->nullable();
            $table->uuid('status_id');

            $table->text('s3_results_key')->nullable();
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();

            $table->foreign('script_id')
                  ->references('id')
                  ->on('test_scripts')
                  ->onDelete('cascade');

            $table->foreign('initiator_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('environment_id')
                  ->references('id')
                  ->on('environments')
                  ->onDelete('set null');

            $table->foreign('status_id')
                  ->references('id')
                  ->on('execution_statuses')
                  ->onDelete('restrict');

            $table->timestamp('created_at');
        });

        Schema::create('containers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('execution_id');

            $table->string('container_id');
            $table->string('status');
            $table->jsonb('configuration')->default('{}');
            $table->text('s3_logs_key')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();

            $table->foreign('execution_id')
                  ->references('id')
                  ->on('test_executions')
                  ->onDelete('cascade');

            $table->timestamp('created_at');
        });

        Schema::create('resource_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('container_id');

            $table->float('cpu_usage')->nullable();
            $table->float('memory_usage')->nullable();
            $table->jsonb('additional_metrics')->default('{}');
            $table->timestamp('metric_time');

            $table->foreign('container_id')
                  ->references('id')
                  ->on('containers')
                  ->onDelete('cascade');

            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_metrics');
        Schema::dropIfExists('containers');
        Schema::dropIfExists('test_executions');
        Schema::dropIfExists('execution_statuses');
    }
};
