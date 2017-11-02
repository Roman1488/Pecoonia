<?php
	namespace App\Pecoonia\Calculator\Type;
	
	class PurchaseCurrencyRate extends Base  {

		public function calculateValue()
		{
			$f = $this->_e( function($t) {
				return ( $t->getLocalCurrencyRate() * $t->getUseQuantity() );
			});
			$f = ($f/$this->_t()->getQuantity());
			$this->setValue($f);
		}
	}
