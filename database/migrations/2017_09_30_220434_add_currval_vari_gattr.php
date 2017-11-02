<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCurrvalVariGattr extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guideline_attributes', function (Blueprint $table) {
            $table->double('current_value')->nullable()->default(0.00000)->after('attribute_type');
            $table->double('variance')->nullable()->default(0.00000)->after('current_value');
        });

        \DB::statement('UPDATE guideline_attributes JOIN portfolio_guidelines ON portfolio_guidelines.id = guideline_attributes.guideline_id SET guideline_attributes.current_value = portfolio_guidelines.current_value,
                guideline_attributes.variance = portfolio_guidelines.variance;');

        Schema::table('portfolio_guidelines', function (Blueprint $table) {
            $table->dropColumn('current_value');
            $table->dropColumn('variance');
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
