<?php
	namespace App\Pecoonia\Calculator\Type;
	
	class ProfitLossBookValue extends Base {
		
		public function calculateLocal()
		{
			$this->setLocal( ( $this->calculator()->TradeValue->getLocal() - $this->calculator()->BookValue->getLocal() ) );
		}

		public function calculateBase()
		{
			$this->setBase( ( $this->calculator()->TradeValue->getBase() - $this->calculator()->BookValue->getBase() ) );
		}

	}
