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
        Schema::create('test_cases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('suite_id')->constrained('test_suites')->onDelete('cascade');
            $table->foreignUuid('story_id')->nullable()->constrained('stories')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('steps');
            $table->text('expected_results');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['draft', 'active', 'deprecated', 'archived'])->default('draft');
            $table->json('tags')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_cases');
    }
};
