<?php
	namespace App\Pecoonia\Calculator\Type;
	
	class AverageDaysOwned extends Base {

		public function calculateValue()
		{
			$daysOwned = $this->_e(function($t) {
				return ( $t->getDaysOwned() * $t->getUseQuantity() );
			});

			$this->setValue( $daysOwned / $this->_t()->getQuantity() );
		}

	}
