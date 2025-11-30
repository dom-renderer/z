<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProducitonPlanningImportSheetToSettingsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('cims')->comment('1 = Can import sheet more than once in a shift')->default(0);
        });

        Schema::table('scheduling_imports', function (Blueprint $table) {
            $table->boolean('is_planning')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('cims');
        });

        Schema::table('scheduling_imports', function (Blueprint $table) {
            $table->dropColumn('is_planning');
        });
    }
}
