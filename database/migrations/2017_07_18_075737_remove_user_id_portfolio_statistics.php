<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveUserIdPortfolioStatistics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('portfolio_daily_values', function (Blueprint $table) {
            $table->dropForeign('portfolio_dv_user_id_foreign');
            $table->dropColumn('user_id');
        });
        Schema::table('portfolio_statistics', function (Blueprint $table) {
            $table->dropForeign('portfolio_stat_user_id_foreign');
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('portfolio_daily_values', function(Blueprint $table)
        {
            $table->integer('user_id')->unsigned()->index('portfolio_dv_user_id_foreign');
            $table->foreign('user_id', 'portfolio_dv_user_id_foreign')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
        Schema::table('portfolio_statistics', function(Blueprint $table)
        {
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id', 'portfolio_stat_user_id_foreign')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }
}
