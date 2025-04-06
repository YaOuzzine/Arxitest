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
        Schema::create('integrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->string('name');
            $table->string('base_url')->nullable();
            $table->text('encrypted_credentials')->nullable();
            $table->jsonb('shared_config')->default('{}');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('project_integrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->uuid('integration_id');

            $table->text('encrypted_credentials')->nullable();
            $table->jsonb('project_specific_config')->default('{}');
            $table->boolean('is_active')->default(true);

            $table->foreign('project_id')
                  ->references('id')
                  ->on('projects')
                  ->onDelete('cascade');

            $table->foreign('integration_id')
                  ->references('id')
                  ->on('integrations')
                  ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_integrations');
        Schema::dropIfExists('integrations');
    }
};
