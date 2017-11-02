<?php
	namespace App\Pecoonia\Calculator\Type;
	
	class ProfitLossPurchasePriceExcurrency extends Base {
		public function calculateValue()
		{
			$this->setValue( ( $this->calculator()->ProfitLossPurchaseValue->getBase()-$this->calculator()->CurrencyProfitLossPurchasePrice->getValue() ) );
		}
	}
