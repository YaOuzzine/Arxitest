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
        Schema::create('test_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('script_id')->constrained('test_scripts')->onDelete('cascade');
            $table->string('version_hash');
            $table->text('script_content');
            $table->jsonb('changes')->nullable();
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_versions');
    }
};
