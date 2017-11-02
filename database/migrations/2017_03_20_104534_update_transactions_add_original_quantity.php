<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTransactionsAddOriginalQuantity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('transactions', 'original_quantity')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->bigInteger('original_quantity')->nullable()->default(null)->after('quantity');
            });

            // Initially load values from quantity column
            \DB::statement('UPDATE transactions SET original_quantity = quantity');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('original_quantity');
        });
    }
}
