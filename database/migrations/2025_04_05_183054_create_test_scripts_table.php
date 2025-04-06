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
        Schema::create('test_scripts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('test_case_id')->nullable();
            $table->uuid('creator_id');
            $table->uuid('story_id')->nullable();

            $table->string('name');
            $table->string('framework_type');
            $table->text('script_content');
            $table->jsonb('metadata')->default('{}');

            $table->foreign('test_case_id')
                  ->references('id')
                  ->on('test_cases')
                  ->onDelete('set null');

            $table->foreign('creator_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('story_id')
                  ->references('id')
                  ->on('stories')
                  ->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_scripts');
    }
};
