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
            $table->string('license_category')->nullable();
            $table->integer('corners_per_lap')->nullable();
            $table->string('track_name')->nullable();
            $table->string('config_name')->nullable();
            $table->integer('temp_value')->nullable();
            $table->integer('temp_units')->nullable();
            $table->integer('rel_humidity')->nullable();
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
            $table->dropColumn('license_category')->nullable();
            $table->dropColumn('corners_per_lap')->nullable();
            $table->dropColumn('track_name')->nullable();
            $table->dropColumn('config_name')->nullable();
            $table->dropColumn('temp_value')->nullable();
            $table->dropColumn('temp_units')->nullable();
            $table->dropColumn('rel_humidity')->nullable();
        });
    }
};
