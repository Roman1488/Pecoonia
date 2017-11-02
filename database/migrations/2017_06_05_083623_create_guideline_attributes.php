<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuidelineAttributes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('guideline_attributes'))
        {
            Schema::create('guideline_attributes', function(Blueprint $table)
            {
                $table->bigInteger('id', true)->unsigned();
                $table->integer('portfolio_id')->unsigned()->nullable();
                $table->foreign('portfolio_id', 'guideline_attributes_portfolio_id_foreign')->references('id')->on('portfolios')->onUpdate('RESTRICT')->onDelete('CASCADE');

                $table->bigInteger('guideline_id')->unsigned();
                $table->foreign('guideline_id', 'guideline_attributes_guideline_id_foreign')->references('id')->on('portfolio_guidelines')->onUpdate('RESTRICT')->onDelete('CASCADE');
                $table->string('attribute')->nullable();
                $table->string('attribute_type')->nullable();
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
        Schema::table('guideline_attributes', function(Blueprint $table)
        {
            $table->dropForeign('guideline_attributes_portfolio_id_foreign');
            $table->dropForeign('guideline_attributes_guideline_id_foreign');
        });

        Schema::drop('guideline_attributes');
    }
}