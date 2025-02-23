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
        Schema::create('test_script_data', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('script_id')->constrained('test_scripts')->onDelete('cascade');
            $table->foreignUuid('test_data_id')->constrained('test_data')->onDelete('cascade');
            $table->jsonb('usage_context')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_script_data');
    }
};
