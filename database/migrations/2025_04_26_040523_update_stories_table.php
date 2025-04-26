<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            // Add project_id FK and epic_id FK only if not exists
            if (!Schema::hasColumn('stories', 'project_id')) {
                $table->uuid('project_id')->after('id');
                $table->foreign('project_id')
                      ->references('id')->on('projects')
                      ->onDelete('cascade');
            }

            if (!Schema::hasColumn('stories', 'epic_id')) {
                $table->uuid('epic_id')->nullable()->after('project_id');
                $table->foreign('epic_id')
                      ->references('id')->on('epics')
                      ->onDelete('set null');
            }

            // Add unique constraint for external deduplication
            if (!Schema::hasIndex('stories', 'stories_unique_external')) {
                $table->unique(['source', 'external_id', 'project_id'], 'stories_unique_external');
            }

            // Add GIN index on metadata
            // Laravel's Blueprint->index doesn't support specifying index type, use raw statement
            DB::statement('CREATE INDEX IF NOT EXISTS stories_metadata_gin ON stories USING gin (metadata)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            // Drop FK and columns
            $table->dropForeign(['project_id']);
            $table->dropForeign(['epic_id']);

            // Drop index and unique constraint
            $table->dropUnique('stories_unique_external');
            DB::statement('DROP INDEX IF EXISTS stories_metadata_gin');

            $table->dropColumn(['project_id', 'epic_id']);
        });
    }
};
