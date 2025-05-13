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
        Schema::create('sync_mappings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('arxitest_type');  // Store class name like "App\Models\Story"
            $table->uuid('arxitest_id');      // Reference to the entity in Arxitest
            $table->string('external_system'); // 'jira', 'github', etc.
            $table->string('external_id');    // ID in the external system
            $table->timestamp('last_sync')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            // Add indices for better performance
            $table->index(['external_system', 'external_id']);
            $table->index(['arxitest_type', 'arxitest_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_mappings');
    }
};
