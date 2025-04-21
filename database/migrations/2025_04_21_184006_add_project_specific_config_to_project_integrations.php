<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class AddProjectSpecificConfigToProjectIntegrations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // The project_integrations table already exists with project_specific_config column
        // We just need to ensure it's of the right type - it should be JSON


        Schema::table('project_integrations', function (Blueprint $table) {
            if (!Schema::hasColumn('project_integrations', 'project_specific_config')) {
                $table->json('project_specific_config')->nullable()->after('encrypted_credentials');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // If we need to roll back, we'll clear the mapping data from the config
        // without dropping the column, as it may contain other important data
        DB::table('project_integrations')
            ->whereRaw("JSON_CONTAINS_PATH(project_specific_config, 'one', '$.mappings')")
            ->update([
                'project_specific_config' => DB::raw("JSON_REMOVE(project_specific_config, '$.mappings')")
            ]);
    }
}
