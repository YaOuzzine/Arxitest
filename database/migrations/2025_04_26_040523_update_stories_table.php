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
        Schema::table('stories', function (Blueprint $table) {
            // 1) Link to projects
            $table->uuid('project_id')->after('id');
            $table->foreign('project_id')
                  ->references('id')
                  ->on('projects')
                  ->onDelete('cascade');

            // 2) Optional Epic link
            $table->uuid('epic_id')->nullable()->after('project_id');
            $table->foreign('epic_id')
                  ->references('id')
                  ->on('epics')
                  ->onDelete('set null');

            // 3) Uniqueness to avoid dupes across imports
            $table->unique(['source', 'external_id', 'project_id'], 'stories_unique_external');

            // 4) Index metadata JSONB for performance
            $table->index('metadata', 'stories_metadata_gin', 'gin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['epic_id']);
            $table->dropIndex('stories_metadata_gin');
            $table->dropUnique('stories_unique_external');
            $table->dropColumn(['project_id', 'epic_id']);
        });
    }
};
