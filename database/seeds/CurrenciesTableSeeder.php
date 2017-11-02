<?php

use Illuminate\Database\Seeder;

class CurrenciesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('currencies')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = [
            [
                "name" => "American Dollar",
                "symbol" => "USD",
                "value" => "0.00000",
            ],
            [
                "name" => "Danish Krone",
                "symbol" => "DKK",
                "value" => "0.00000",
            ],
            [
                "name" => "British Pound",
                "symbol" => "GBP",
                "value" => "0.00000",
            ],
            [
                "name" => "Japanese Yen",
                "symbol" => "JPY",
                "value" => "0.00000",
            ],
            [
                "name" => "European Euro",
                "symbol" => "EUR",
                "value" => "0.00000",
            ],
            [
                "name" => "Swiss Franc",
                "symbol" => "CHF",
                "value" => "0.00000",
            ],
            [
                "name" => "Swedish Krona",
                "symbol" => "SEK",
                "value" => "0.00000",
            ],
            [
                "name" => "Norwegian Krone",
                "symbol" => "NOK",
                "value" => "0.00000",
            ],
            [
                "name" => "Canadian Dollar",
                "symbol" => "CAD",
                "value" => "0.00000",
            ],
            [
                "name" => "Taiwan Dollar",
                "symbol" => "TWD",
                "value" => "0.00000",
            ],
            [
                "name" => "Korean Won",
                "symbol" => "KRW",
                "value" => "0.00000",
            ],
            [
                "name" => "Australian Dollar",
                "symbol" => "AUD",
                "value" => "0.00000",
            ],
            [
                "name" => "Hong Kong Dollar",
                "symbol" => "HKD",
                "value" => "0.00000",
            ],
            [
                "name" => "Malaysian Ringgit",
                "symbol" => "MYR",
                "value" => "0.00000",
            ],
            [
                "name" => "Indonesian Rupiah",
                "symbol" => "IDR",
                "value" => "0.00000",
            ],
            [
                "name" => "Russian Ruble",
                "symbol" => "RUB",
                "value" => "0.00000",
            ],
            [
                "name" => "Brazilian Real",
                "symbol" => "BRL",
                "value" => "0.00000",
            ],
            [
                "name" => "Argentina Peso",
                "symbol" => "ARS",
                "value" => "0.00000",
            ],
            [
                "name" => "Mexican Peso",
                "symbol" => "MXN",
                "value" => "0.00000",
            ],
            [
                "name" => "Indian Rupee",
                "symbol" => "INR",
                "value" => "0.00000",
            ],
            [
                "name" => "New Zealand Dollar",
                "symbol" => "NZD",
                "value" => "0.00000",
            ],
            [
                "name" => "Singapore Dollar",
                "symbol" => "SGD",
                "value" => "0.00000",
            ],
            [
                "name" => "Chinese Yuan",
                "symbol" => "CNY",
                "value" => "0.00000",
            ],
            [
                "name" => "South African Rand",
                "symbol" => "ZAR",
                "value" => "0.00000",
            ],
            [
                "name" => "Turkish Lira",
                "symbol" => "TRY",
                "value" => "0.00000",
            ],
        ];

        DB::table('currencies')->insert($data);
    }
}
