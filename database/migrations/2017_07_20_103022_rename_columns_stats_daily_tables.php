<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameColumnsStatsDailyTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('portfolio_daily_values', function (Blueprint $table) {
            $table->renameColumn('portfolio_change', 'marketvalue_change');
            $table->renameColumn('portfolio_percent_change', 'marketvalue_percent_change');
        });
        Schema::table('portfolio_statistics', function (Blueprint $table) {
            $table->renameColumn('portfolio_change', 'marketvalue_change');
            $table->renameColumn('portfolio_percent_change', 'marketvalue_percent_change');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
