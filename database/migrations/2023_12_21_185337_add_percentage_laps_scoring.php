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
        Schema::table('scoring', function (Blueprint $table) {
            $table->boolean('enabled_percentage_laps')->after('races_to_drop')->default(false);
            $table->integer('lap_percentage_to_complete')->after('enabled_percentage_laps')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('scoring', function (Blueprint $table) {
            $table->dropColumn('enabled_percentage_laps');
            $table->dropColumn('lap_percentage_to_complete');
        });
    }
};
