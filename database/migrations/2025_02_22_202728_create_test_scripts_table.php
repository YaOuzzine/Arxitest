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
            $table->foreignUuid('suite_id')->constrained('test_suites')->onDelete('cascade');
            $table->foreignUuid('creator_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('jira_story_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('framework_type');
            $table->text('script_content');
            $table->jsonb('metadata')->nullable();
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
