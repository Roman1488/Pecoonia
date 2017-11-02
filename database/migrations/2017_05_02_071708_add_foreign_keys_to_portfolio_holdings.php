<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysToPortfolioHoldings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('portfolio_holdings', function(Blueprint $table)
        {
            $table->foreign('portfolio_id', 'portfolio_hl_portfolio_id_foreign')->references('id')->on('portfolios')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('portfolio_holdings', function(Blueprint $table)
        {
            $table->dropForeign('portfolio_hl_portfolio_id_foreign');
        });
    }

}
