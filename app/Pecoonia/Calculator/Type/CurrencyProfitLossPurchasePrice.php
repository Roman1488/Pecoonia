<?php
	namespace App\Pecoonia\Calculator\Type;

	class CurrencyProfitLossPurchasePrice extends Base {
		public function calculateValue()
		{
			$this->setValue( ( $this->_t()->getLocalCurrencyRate()-$this->calculator()->PurchaseCurrencyRate->getValue() )*$this->calculator()->TradeValue->getLocal() );
		}
	}
