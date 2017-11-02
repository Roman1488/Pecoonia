<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePortfolioStatsCurrencyDistribution extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('portfolio_stats_currency_distribution'))
        {
            Schema::create('portfolio_stats_currency_distribution', function(Blueprint $table)
            {
                $table->bigInteger('id', true)->unsigned();
                $table->bigInteger('portfolio_statistics_id')->unsigned()->nullable()->index('portfolio_statistics_id_foreign');
                $table->foreign('portfolio_statistics_id', 'portfolio_statistics_id_foreign')->references('id')->on('portfolio_statistics')->onUpdate('RESTRICT')->onDelete('CASCADE');
                $table->string('currency_symbol')->nullable();
                $table->decimal('market_value_base', 15, 5)->nullable()->default(0.00000);
                $table->decimal('bank_balance_base', 15, 5)->nullable()->default(0.00000);
                $table->decimal('percentage_share')->nullable()->default(0.00000);
                $table->timestamps();
            });
        }
    }


    public function down()
    {
        Schema::table('portfolio_stats_currency_distribution', function(Blueprint $table)
        {
            $table->dropForeign('portfolio_statistics_id_foreign');
        });
        Schema::drop('portfolio_stats_currency_distribution');
    }
}