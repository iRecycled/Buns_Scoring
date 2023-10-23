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
            $table->text('qualifying')->after('season_id')->nullable();
            $table->text('heat')->after('qualifying')->nullable();
            $table->text('consolation')->after('heat')->nullable();
            $table->text('feature')->after('consolation')->nullable();
            $table->integer('fastest_lap')->after('feature')->nullable();
            $table->boolean('enabled_drop_weeks')->after('fastest_lap')->nullable();
            $table->integer('drop_weeks_start')->after('enabled_drop_weeks')->nullable();
            $table->integer('races_to_drop')->after('drop_weeks_start')->nullable();
            $table->dropColumn('scoring_json');
            $table->dropColumn('race_type');
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
            $table->text('scoring_json');
            $table->text('race_type');
            $table->dropColumn('qualifying')->nullable();
            $table->dropColumn('heat')->nullable();
            $table->dropColumn('consolation')->nullable();
            $table->dropColumn('feature')->nullable();
            $table->dropColumn('fastest_lap')->nullable();
            $table->dropColumn('enabled_drop_weeks')->nullable();
            $table->dropColumn('drop_weeks_start')->nullable();
            $table->dropColumn('races_to_drop')->nullable();
        });
    }
};
