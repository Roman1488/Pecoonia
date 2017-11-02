<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToPortfolioHoldings extends Migration
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
            $table->foreign('user_id', 'portfolio_holdings_user_id_foreign')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
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
            $table->dropForeign('portfolio_holdings_user_id_foreign');
        });
    }
}
