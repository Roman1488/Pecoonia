<?php
	namespace App\Pecoonia\Calculator\Type;

	class Dividend extends Base {
		protected function calculateLocal()
		{
			if( $this->_t()->isSameCurrency() )
			{
				$this->setLocal($this->_t()->getDividend());
			} else {
				
				if( $this->_t()->isTaxIncluded() )
				{
					$this->setLocal($this->_t()->getDividend());
				} else {
					$this->setLocal( ( $this->_t()->getDividend() * 1) + $this->_t()->getTax() );
				}
			}
		}

		protected function calculateBase()
		{
			if( $this->_t()->isSameCurrency() )
			{
				if( $this->_t()->isTaxIncluded() )
				{
					$this->setBase( ( $this->_t()->getDividend() * 1) );
				} else {
					$this->setBase( ( $this->_t()->getDividend() * 1) + $this->_t()->getTax() );
				}
			} else {
				if( $this->_t()->isTaxIncluded() )
				{
					$this->setBase( ( $this->_t()->getDividend() * $this->_t()->getLocalCurrencyRate() ) );
				} else {
					$this->setBase( ($this->_t()->getDividend() * $this->_t()->getLocalCurrencyRate() ) + $this->_t()->getTax() );
				}
			}
		}
	}
