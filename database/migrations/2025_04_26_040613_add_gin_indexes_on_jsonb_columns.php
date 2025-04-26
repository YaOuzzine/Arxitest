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
        Schema::table('test_suites', function (Blueprint $table) {
            $table->index('settings', 'test_suites_settings_gin', 'gin');
        });

        Schema::table('test_scripts', function (Blueprint $table) {
            $table->index('metadata', 'test_scripts_metadata_gin', 'gin');
        });

        Schema::table('test_data', function (Blueprint $table) {
            $table->index('metadata', 'test_data_metadata_gin', 'gin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_suites', function (Blueprint $table) {
            $table->dropIndex('test_suites_settings_gin');
        });

        Schema::table('test_scripts', function (Blueprint $table) {
            $table->dropIndex('test_scripts_metadata_gin');
        });

        Schema::table('test_data', function (Blueprint $table) {
            $table->dropIndex('test_data_metadata_gin');
        });
    }
};
