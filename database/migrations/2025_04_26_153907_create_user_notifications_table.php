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
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->uuid('notification_id');
            $table->uuid('user_id');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->primary(['notification_id', 'user_id']);
            $table->foreign('notification_id')->references('id')->on('notifications')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // first drop FKs on the pivot
        Schema::table('user_notifications', function (Blueprint $table) {
            $table->dropForeign(['notification_id']);
            $table->dropForeign(['user_id']);
        });

        // then drop the pivot table itself
        Schema::dropIfExists('user_notifications');
    }
};
