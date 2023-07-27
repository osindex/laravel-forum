<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusForForum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forum_categories', function (Blueprint $table) {
            $table->unsignedTinyInteger('status')->nullable()->default(0)->comment('0 check 1pass 2hide')->after('is_private');
        });
        Schema::table('forum_threads', function (Blueprint $table) {
            $table->string('status', 10)->default('show')->comment('stash,show')->after('locked');
        });
        Schema::table('forum_posts', function (Blueprint $table) {
            $table->string('status', 10)->default('show')->comment('stash,show')->after('sequence');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('forum_categories', function (Blueprint $table) {
            $table->dropColumn(['status']);
        });
        Schema::table('forum_threads', function (Blueprint $table) {
            $table->dropColumn(['status']);
        });
        Schema::table('forum_posts', function (Blueprint $table) {
            $table->dropColumn(['status']);
        });
    }
}
