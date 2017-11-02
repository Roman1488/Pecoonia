<?php
	namespace App\Pecoonia\Calculator\Type;

	class ProfitLossBookValueExcurrency extends Base {
		public function calculateValue()
		{
			$this->setValue( ( $this->calculator()->ProfitLossBookValue->getBase()-$this->calculator()->CurrencyProfitLossBookValue->getValue() ) );
		}
	}
