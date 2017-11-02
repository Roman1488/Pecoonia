<?php
	namespace App\Pecoonia\Calculator\Type;
	
	class ReturnBookValue extends Base {
		
		public function calculateLocal()
		{
			$this->setLocal( ( ( $this->calculator()->TradeValue->getLocal() - $this->calculator()->BookValue->getLocal() ) / ($this->calculator()->BookValue->getLocal() ?: 1) ) * 100 );
		}

		public function calculateBase()
		{
			$this->setBase( ( ( $this->calculator()->TradeValue->getBase() - $this->calculator()->BookValue->getBase() ) / ($this->calculator()->BookValue->getBase() ?: 1) ) * 100 );
		}

	}
