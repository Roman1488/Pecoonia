<?php
	namespace App\Pecoonia\Calculator\Type;

	class NetDividend extends Base {
		protected function calculateLocal()
		{
			if( $this->_t()->isSameCurrency() )
			{
				$this->setLocal($this->_t()->getDividend());
			} else {
				
				if( $this->_t()->isTaxIncluded() )
				{
					$this->setLocal( $this->_t()->getDividend() - $this->_t()->getTax() );
				} else {
					$this->setLocal( $this->_t()->getDividend() );
				}
			}
		}

		protected function calculateBase()
		{
			if( $this->_t()->isSameCurrency() )
			{
				if( $this->_t()->isTaxIncluded() )
				{
					$this->setBase( ( $this->_t()->getDividend() - $this->_t()->getTax() ) );
				} else {
					$this->setBase( $this->_t()->getDividend() );
				}
			} else {
				if( $this->_t()->isTaxIncluded() )
				{
					$this->setBase( ( $this->_t()->getDividend() - $this->_t()->getTax() ) * $this->_t()->getLocalCurrencyRate() );
				} else {
					$this->setBase( $this->_t()->getDividend() * $this->_t()->getLocalCurrencyRate() );
				}
			}
		}
	}
