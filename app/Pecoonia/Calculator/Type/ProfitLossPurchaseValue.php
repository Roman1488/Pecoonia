<?php
	namespace App\Pecoonia\Calculator\Type;
	

	class ProfitLossPurchaseValue extends Base {
		
		public function calculateLocal()
		{
			$this->setLocal( ( $this->calculator()->TradeValue->getLocal() - $this->calculator()->PurchaseValue->getLocal() ) );
		}

		public function calculateBase()
		{
			$this->setBase( ( $this->calculator()->TradeValue->getBase() - $this->calculator()->PurchaseValue->getBase() ) );
		}

	}
