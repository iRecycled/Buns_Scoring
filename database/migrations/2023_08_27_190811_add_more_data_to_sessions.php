<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->integer('laps_lead');
            $table->integer('laps_completed');
            $table->string('average_lap_time')->nullable();
            $table->string('best_lap_time')->nullable();
            $table->integer('best_lap_number')->nullable();
            $table->string('qualifying_lap_time')->nullable();
            $table->integer('starting_pos')->nullable();
            $table->string('interval')->nullable();
            $table->string('incidents');
            $table->string('club_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropColumn('laps_lead');
            $table->dropColumn('laps_completed');
            $table->dropColumn('average_lap_time');
            $table->dropColumn('best_lap_time');
            $table->dropColumn('best_lap_number');
            $table->dropColumn('qualifying_lap_time');
            $table->dropColumn('starting_pos');
            $table->dropColumn('interval');
            $table->dropColumn('incidents');
            $table->dropColumn('club_name');
        });
    }
};
