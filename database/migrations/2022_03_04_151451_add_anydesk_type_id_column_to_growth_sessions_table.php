<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAnydeskTypeIdColumnToGrowthSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('growth_sessions', function (Blueprint $table) {
            $table->string('anydesks_remote_desk_id')->nullable(true)->after('discord_channel_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(Schema::hasColumn('growth_sessions', 'anydesks_remote_desk_id')) {
            Schema::table('growth_sessions', function (Blueprint $table) {
                $table->dropColumn('anydesks_remote_desk_id');
            });
        }
    }
}
