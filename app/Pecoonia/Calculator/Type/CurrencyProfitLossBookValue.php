<?php
	namespace App\Pecoonia\Calculator\Type;
	
	class CurrencyProfitLossBookValue extends Base {
		public function calculateValue()
		{
			$this->setValue( ( $this->_t()->getLocalCurrencyRate()-$this->calculator()->BookCurrencyRate->getValue() )*$this->calculator()->TradeValue->getLocal() );
		}
	}
