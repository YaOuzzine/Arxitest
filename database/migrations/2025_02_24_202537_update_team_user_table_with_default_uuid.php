<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('team_user', function (Blueprint $table) {
            // Remove the primary key constraint
            DB::statement('ALTER TABLE team_user DROP CONSTRAINT team_user_pkey');

            // Update the id column to have a default value
            DB::statement('ALTER TABLE team_user ALTER COLUMN id SET DEFAULT gen_random_uuid()');

            // Re-add the primary key constraint
            DB::statement('ALTER TABLE team_user ADD PRIMARY KEY (id)');
        });
    }

    public function down()
    {
        Schema::table('team_user', function (Blueprint $table) {
            // Remove default value if needed
            DB::statement('ALTER TABLE team_user ALTER COLUMN id DROP DEFAULT');
        });
    }
};
