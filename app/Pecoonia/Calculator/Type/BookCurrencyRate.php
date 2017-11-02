<?php
	namespace App\Pecoonia\Calculator\Type;
	
	class BookCurrencyRate extends Base {
		protected function calculateValue()
		{
			$f = $this->_e( function($t) {
				return ( $t->getLocalCurrencyRateBookValue() * $t->getUseQuantity() );
			});
			$f = ($f/$this->_t()->getQuantity());
			$this->setValue($f);
		}
	}
