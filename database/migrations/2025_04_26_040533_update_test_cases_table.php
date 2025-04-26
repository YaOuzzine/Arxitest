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
        // Ensure doctrine/dbal is installed for change()
        Schema::table('test_cases', function (Blueprint $table) {
            // Change story_id to non-nullable and cascade
            $table->dropForeign(['story_id']);
            $table->uuid('story_id')->nullable(false)->change();
            $table->foreign('story_id')
                  ->references('id')->on('stories')
                  ->onDelete('cascade');

            // Change suite_id to nullable
            $table->dropForeign(['suite_id']);
            $table->uuid('suite_id')->nullable()->change();
            $table->foreign('suite_id')
                  ->references('id')->on('test_suites')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_cases', function (Blueprint $table) {
            // Revert story_id to nullable + set null on delete
            $table->dropForeign(['story_id']);
            $table->uuid('story_id')->nullable()->change();
            $table->foreign('story_id')
                  ->references('id')->on('stories')
                  ->onDelete('set null');

            // Revert suite_id to non-nullable
            $table->dropForeign(['suite_id']);
            $table->uuid('suite_id')->nullable(false)->change();
            $table->foreign('suite_id')
                  ->references('id')->on('test_suites')
                  ->onDelete('cascade');
        });
    }
};
