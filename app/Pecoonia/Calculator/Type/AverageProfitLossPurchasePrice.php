<?php
	namespace App\Pecoonia\Calculator\Type;
	
	class AverageProfitLossPurchasePrice extends Base {

		public function calculateValue()
		{
			$this->setValue( $this->calculator()->TradeValue->getBase() - ( $this->calculator()->AveragePurchasePrice->getValue() * $this->_t()->getQuantity() ) );
		}

	}
