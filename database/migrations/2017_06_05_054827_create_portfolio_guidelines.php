<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePortfolioGuidelines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('portfolio_guidelines'))
        {
            Schema::create('portfolio_guidelines', function(Blueprint $table)
            {
                $table->bigInteger('id', true)->unsigned();
                $table->integer('portfolio_id')->unsigned()->nullable();
                $table->foreign('portfolio_id', 'portfolio_guidelines_portfolio_id_foreign')->references('id')->on('portfolios')->onUpdate('RESTRICT')->onDelete('CASCADE');
                $table->string('guideline')->nullable();
                $table->double('min')->nullable()->default(0.00000);
                $table->double('max')->nullable()->default(0.00000);
                $table->double('current_value')->nullable()->default(0.00000);
                $table->double('variance')->nullable()->default(0.00000);
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
        Schema::table('portfolio_guidelines', function(Blueprint $table)
        {
            $table->dropForeign('portfolio_guidelines_portfolio_id_foreign');
        });

        Schema::drop('portfolio_guidelines');
    }
}
