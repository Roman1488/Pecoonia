<?php
	namespace App\Pecoonia\Calculator\Type;

	class PurchaseValue extends Base {
		
		protected function calculateLocal()
		{
			if( $this->_t()->isSameCurrency() )
			{
				$this->setLocal(0);
			} else {
				
				$fifo = $this->_e( function($t)
				{
					return ( $t->getValue() / $t->getQuantity() ) * $t->getUseQuantity();
				});

				$this->setLocal( ( $fifo * 1 ) );
			}
		}

		protected function calculateBase()
		{
			if( $this->_t()->isSameCurrency() )
			{
				$fifo = $this->_e( function($t)
				{
					return ( $t->getValue() / $t->getQuantity() ) * $t->getUseQuantity();
				});

				$this->setBase( ( $fifo * 1 ) );
			} else {
				$fifo = $this->_e( function($t)
				{
					return ( ( $t->getValue() * $t->getLocalCurrencyRate() ) / $t->getQuantity() ) * $t->getUseQuantity();
				});

				$this->setBase($fifo);
			}
		}

	}
