<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePortfolioDailyValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('portfolio_daily_values', function(Blueprint $table) {
            $table->decimal('portfolio_value', 15, 5)->nullable()->default(0.00000)->after('total_all_bank_balance');
            $table->decimal('cash_share', 15, 5)->nullable()->default(0.00000)->after('portfolio_value');
            $table->decimal('equity_share', 15, 5)->nullable()->default(0.00000)->after('cash_share');
            $table->decimal('fund_share', 15, 5)->nullable()->default(0.00000)->after('equity_share');
            $table->integer('securities_up')->nullable()->default(0)->after('fund_share');
            $table->integer('securities_down')->nullable()->default(0)->after('securities_up');
            $table->integer('securities_unchanged')->nullable()->default(0)->after('securities_down');
            $table->decimal('portfolio_change', 15, 5)->nullable()->default(0.00000)->after('securities_unchanged');
            $table->decimal('portfolio_percent_change', 15, 5)->nullable()->default(0.00000)->after('portfolio_change');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('portfolio_daily_values', function (Blueprint $table) {
            $table->dropColumn('portfolio_value');
            $table->dropColumn('cash_share');
            $table->dropColumn('equity_share');
            $table->dropColumn('fund_share');
            $table->dropColumn('securities_up');
            $table->dropColumn('securities_down');
            $table->dropColumn('securities_unchanged');
            $table->dropColumn('portfolio_change');
            $table->dropColumn('portfolio_percent_change');
        });
    }
}
