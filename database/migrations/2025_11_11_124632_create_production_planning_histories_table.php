<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductionPlanningHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('production_planning_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('uom_id');
            $table->unsignedBigInteger('shift_id')->nullable();
            $table->double('sales_order')->default(0);
            $table->double('indent')->default(0);
            $table->double('total')->default(0);
            $table->double('opening_stock')->default(0);
            $table->double('production')->default(0);
            $table->unsignedBigInteger('added_by');
            $table->dateTime('shift_time')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('production_planning_histories');
    }
}
