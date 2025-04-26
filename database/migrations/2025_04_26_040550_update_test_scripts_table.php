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
        Schema::table('test_scripts', function (Blueprint $table) {
            // Drop the story link
            $table->dropForeign(['story_id']);
            $table->dropColumn('story_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_scripts', function (Blueprint $table) {
            // Restore story_id if needed
            $table->uuid('story_id')->nullable()->after('creator_id');
            $table->foreign('story_id')
                  ->references('id')
                  ->on('stories')
                  ->onDelete('set null');
        });
    }
};
