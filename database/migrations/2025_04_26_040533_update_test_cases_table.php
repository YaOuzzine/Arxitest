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
        Schema::table('test_cases', function (Blueprint $table) {
            // 1) Enforce every case must belong to a story
            $table->dropForeign(['story_id']);
            $table->foreignUuid('story_id')
                  ->constrained('stories')
                  ->onDelete('cascade')
                  ->change();

            // 2) Allow suite_id to be nullable for flexibility
            $table->dropForeign(['suite_id']);
            $table->foreignUuid('suite_id')
                  ->nullable()
                  ->constrained('test_suites')
                  ->onDelete('cascade')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_cases', function (Blueprint $table) {
            // Revert story_id back to nullable
            $table->dropForeign(['story_id']);
            $table->foreignUuid('story_id')
                  ->nullable()
                  ->constrained('stories')
                  ->onDelete('set null');

            // Revert suite_id back to required
            $table->dropForeign(['suite_id']);
            $table->foreignUuid('suite_id')
                  ->constrained('test_suites')
                  ->onDelete('cascade');
        });
    }
};
