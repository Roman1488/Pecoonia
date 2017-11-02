<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SecurityWatchlist extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('security_watchlist', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('portfolio_id')->unsigned()->index('security_watchlist_portfolio_id_foreign');
            $table->bigInteger('security_id')->unsigned()->nullable()->index('security_watchlist_security_id_foreign');

            $table->foreign('portfolio_id')->references('id')->on('portfolios')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('security_id')->references('id')->on('securities')->onUpdate('RESTRICT')->onDelete('RESTRICT');

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
        Schema::drop('security_watchlist');
    }

}
