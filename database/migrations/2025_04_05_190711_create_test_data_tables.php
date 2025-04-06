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
        Schema::create('test_data', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('content');
            $table->string('format');
            $table->boolean('is_sensitive')->default(false);
            $table->jsonb('metadata')->default('{}');
            $table->timestamps();
        });


        Schema::create('test_script_data', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('script_id');
            $table->uuid('test_data_id');

            $table->jsonb('usage_context')->default('{}');

            $table->foreign('script_id')
                  ->references('id')
                  ->on('test_scripts')
                  ->onDelete('cascade');

            $table->foreign('test_data_id')
                  ->references('id')
                  ->on('test_data')
                  ->onDelete('cascade');

            $table->timestamp('created_at');
        });

        Schema::create('test_case_data', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('test_case_id');
            $table->uuid('test_data_id');

            $table->jsonb('usage_context')->default('{}');

            $table->foreign('test_case_id')
                  ->references('id')
                  ->on('test_cases')
                  ->onDelete('cascade');

            $table->foreign('test_data_id')
                  ->references('id')
                  ->on('test_data')
                  ->onDelete('cascade');

            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_case_data');
        Schema::dropIfExists('test_script_data');
        Schema::dropIfExists('test_data');
    }
};
