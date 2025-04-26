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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('actor_id')->nullable()->index();      // who triggered it
            $table->string('type');                             // e.g. 'comment', 'build_failed', etc.
            $table->jsonb('data')->nullable();                  // { “post_id”:…, “message”:… }
            $table->timestamp('created_at')->useCurrent();
            // foreign key if you like:
            $table->foreign('actor_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // drop FK on actor_id
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['actor_id']);
        });

        // then drop the notifications table
        Schema::dropIfExists('notifications');
    }
};
