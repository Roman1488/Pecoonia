<?php
	namespace App\Pecoonia\Calculator;

	interface TransactionContract {

		// trade_value for security type
		public function getValue();
		// main quantity
		public function getQuantity();
		// How many to actually use?
		public function getUseQuantity();
		// Comission 
		public function getCommision();
		// Check if commision is included
		public function isCommisionIncluded();
		// Same currency as portfolio?
		public function isSameCurrency();
		// Local currency rate
		public function getLocalCurrencyRate();
		// Local currency rate book value
		public function getLocalCurrencyRateBookValue();
		// book value
		public function getBookValue();
		// Days owned
		public function getDaysOwned();
		// Get dividend
		public function getDividend();
		// Get tax
		public function getTax();
		// Has tax?
		public function isTaxIncluded();
	}
