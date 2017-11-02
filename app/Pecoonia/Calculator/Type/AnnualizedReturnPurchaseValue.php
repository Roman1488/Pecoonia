<?php
	namespace App\Pecoonia\Calculator\Type;

	class AnnualizedReturnPurchaseValue extends Base {
		
		public function calculateLocal()
		{
			$this->setLocal( $this->calculator()->DailyReturnPurchaseValue->getLocal() * 360 );
		}

		public function calculateBase()
		{
			$this->setBase( $this->calculator()->DailyReturnPurchaseValue->getBase() * 360 );
		}
		
	}
