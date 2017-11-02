<?php
	namespace App\Pecoonia\Calculator\Type;

	class Tax extends Base {
		protected function calculateLocal()
		{
			if( $this->_t()->isSameCurrency() )
			{
				$this->setLocal(0);
			} else {
				$this->setLocal( $this->_t()->getTax() );
			}
		}

		protected function calculateBase()
		{
			if( $this->_t()->isSameCurrency() )
			{
				$this->setBase( $this->_t()->getTax() );
			} else {
				$this->setBase( $this->_t()->getTax() * $this->_t()->getLocalCurrencyRate() );
			}
		}
	}
