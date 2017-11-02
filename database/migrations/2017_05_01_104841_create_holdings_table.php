<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHoldingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('portfolio_holdings', function(Blueprint $table)
        {
            $table->bigInteger('id', true)->unsigned();
            $table->integer('portfolio_id')->unsigned()->nullable()->index('transactions_portfolio_id_foreign');
            $table->integer('bank_id')->unsigned()->nullable()->index('transactions_bank_id_foreign');
            $table->bigInteger('security_id')->unsigned()->nullable()->index('transactions_security_id_foreign');
            $table->dateTime('date');
            $table->string('security_name')->nullable();
            $table->decimal('total_inventory', 15, 5)->nullable()->default(0.00000);
            $table->decimal('purchase_value', 15, 5)->nullable()->default(0.00000);
            $table->decimal('app', 15, 5)->nullable()->default(0.00000);
            $table->decimal('price')->nullable()->default(0.00000);
            $table->decimal('market_value')->nullable()->default(0.00000);
            $table->decimal('gain_loss')->nullable()->default(0.00000);
            $table->decimal('return')->nullable()->default(0.00000);
            $table->decimal('market_value_in_base')->nullable()->default(0.00000);
            $table->decimal('gain_loss_in_base')->nullable()->default(0.00000);
            $table->decimal('weight')->nullable()->default(0.00000);
            $table->string('currency_symbol')->nullable()->default(0.00000);
            $table->string('security_type')->nullable()->comment('dividend, split');
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
        Schema::drop('portfolio_holdings');
    }
}
