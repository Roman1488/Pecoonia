<?php

namespace App\Services;

use App;
use App\Transaction;

class StockSplitService
{
    public static function updateTransactions($stockSplits, $security_id)
    {
        //this will check for split entries and then updates the quantity, inventory and last_stock_split_update columns in transactions table

        $transactionTypes = ['buy'];

        //sort $stockSplits by date in accending order

        usort($stockSplits, function ( $a, $b ) {
            return strtotime($a["date"]) - strtotime($b["date"]);
        });

        //here $stockSplits is the array of all stock splits from yahoo

        foreach ($stockSplits as $value) {

            $setOfTransactions = Transaction::where('security_id', $security_id)
                                            ->where(function($query) use ($value)
                                            {
                                                $query->where('last_stock_split_update', '<', $value['date'])
                                                        ->orWhere('last_stock_split_update', NULL);
                                            })
                                            ->where('date', '<', $value['date'])
                                            ->where('inventory', '>', 0)
                                            ->whereIn('transaction_type', $transactionTypes)
                                            ->get();

            $splitRatio = explode(':', $value['value']);

            foreach ($setOfTransactions as $transactionToUpdate)
            {
                $newQuantity  = floor(($transactionToUpdate->quantity * $splitRatio[0]) / $splitRatio[1]);
                $newInventory = floor(($transactionToUpdate->inventory * $splitRatio[0]) / $splitRatio[1]);

                $transactionToUpdate->quantity = $newQuantity;
                $transactionToUpdate->inventory = $newInventory;
                $transactionToUpdate->last_stock_split_update = $value['date'];

                $transactionToUpdate->save();
            }
        }
    }
}