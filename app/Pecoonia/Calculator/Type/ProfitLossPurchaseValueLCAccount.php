<?php
	namespace App\Pecoonia\Calculator\Type;
	
	class ProfitLossPurchaseValueLCAccount extends Base {
		
		public function calculateValue()
		{
			$this->setValue( $this->calculator()->ProfitLossPurchaseValue->getLocal() * $this->_t()->getLocalCurrencyRate() );
		}

	}
