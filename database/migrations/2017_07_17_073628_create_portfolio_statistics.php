<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePortfolioStatistics extends Migration
{
    public function up()
    {
        if(!Schema::hasTable('portfolio_statistics'))
        {
            Schema::create('portfolio_statistics', function(Blueprint $table)
            {
                $table->bigInteger('id', true)->unsigned();
                $table->integer('portfolio_id')->unsigned();
                $table->foreign('portfolio_id', 'portfolio_stat_portfolio_id_foreign')->references('id')->on('portfolios')->onUpdate('RESTRICT')->onDelete('CASCADE');
                $table->integer('user_id')->unsigned();
                $table->foreign('user_id', 'portfolio_stat_user_id_foreign')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
                $table->decimal('total_marketvalue_base', 15, 5)->nullable()->default(0.00000);
                $table->decimal('total_all_bank_balance', 15, 5)->nullable()->default(0.00000);

                $table->decimal('portfolio_value', 15, 5)->nullable()->default(0.00000);
                $table->decimal('cash_share', 15, 5)->nullable()->default(0.00000);
                $table->decimal('equity_share', 15, 5)->nullable()->default(0.00000);
                $table->decimal('fund_share', 15, 5)->nullable()->default(0.00000);
                $table->integer('securities_up')->nullable()->default(0);
                $table->integer('securities_down')->nullable()->default(0);
                $table->integer('securities_unchanged')->nullable()->default(0);
                $table->decimal('portfolio_change', 15, 5)->nullable()->default(0.00000);
                $table->decimal('portfolio_percent_change', 15, 5)->nullable()->default(0.00000);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('portfolio_statistics', function(Blueprint $table)
        {
            $table->dropForeign('portfolio_stat_portfolio_id_foreign');
            $table->dropForeign('portfolio_stat_user_id_foreign');
        });

        Schema::drop('portfolio_statistics');
    }
}