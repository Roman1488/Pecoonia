<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToCurrencyDistributionTable extends Migration
{
     /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('portfolio_daily_currency_distribution', function(Blueprint $table)
        {
            $table->foreign('portfolio_daily_values_id', 'portfolio_daily_values_id_foreign')->references('id')->on('portfolio_daily_values')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('portfolio_daily_currency_distribution', function(Blueprint $table)
        {
            $table->dropForeign('portfolio_daily_values_id_foreign');
        });
    }

}
