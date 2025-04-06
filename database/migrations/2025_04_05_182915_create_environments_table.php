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
        Schema::create('environments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->jsonb('configuration')->default('{}');
            $table->boolean('is_global')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });


        Schema::create('environment_project', function (Blueprint $table) {
            $table->uuid('environment_id');
            $table->uuid('project_id');

            $table->primary(['environment_id', 'project_id']);

            $table->foreign('environment_id')
                  ->references('id')
                  ->on('environments')
                  ->onDelete('cascade');

            $table->foreign('project_id')
                  ->references('id')
                  ->on('projects')
                  ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('environment_project');
        Schema::dropIfExists('environments');
    }
};
