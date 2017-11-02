<?php
	namespace App\Pecoonia\Calculator\Type;
	
	class AveragePurchasePrice extends Base {
		
		public function calculateValue()
		{
			$firstFifo = $this->_e(function($t) {
				if( $t->isSameCurrency() )
					return $t->getValue();
				else
					return ($t->getValue()*$t->getLocalCurrencyRate());
			});

			$secondFifo = $this->_e(function($t)
			{
				return $t->getQuantity();
			});

            if($secondFifo==0)
                $this->setValue( $firstFifo/1);
            else
			    $this->setValue( $firstFifo/$secondFifo );
		}

	}
