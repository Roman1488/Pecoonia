<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCurrencyDistributionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('portfolio_daily_currency_distribution'))
        {
            Schema::create('portfolio_daily_currency_distribution', function(Blueprint $table)
            {
                $table->bigInteger('id', true)->unsigned();
                $table->bigInteger('portfolio_daily_values_id')->unsigned()->nullable()->index('portfolio_daily_values_id_foreign');
                $table->string('currency_symbol')->nullable();
                $table->decimal('market_value_base', 15, 5)->nullable()->default(0.00000);
                $table->decimal('bank_balance_base', 15, 5)->nullable()->default(0.00000);
                $table->decimal('percentage_share')->nullable()->default(0.00000);
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
        Schema::drop('portfolio_daily_currency_distribution');
    }
}
