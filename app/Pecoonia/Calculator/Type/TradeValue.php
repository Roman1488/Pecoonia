<?php
	namespace App\Pecoonia\Calculator\Type;
	
	/**
	 * Used to calculate the trade value
	 */
	class TradeValue extends Base {

		protected function calculateLocal()
		{
			if( $this->_t()->isSameCurrency() )
				$this->setLocal($this->_t()->getValue());
			else
			{
				if( !$this->_t()->isCommisionIncluded() )
					$this->setLocal( ($this->_t()->getValue() + $this->_t()->getCommision())*1 );
				else
					$this->setLocal($this->_t()->getValue());
			}
		}

		protected function calculateBase()
		{
			if( $this->_t()->isSameCurrency() )
			{
				if( $this->_t()->isCommisionIncluded() )
				{
					$this->setBase( ($this->_t()->getValue() * 1) );
				} else {
					$this->setBase( ($this->_t()->getValue() + $this->_t()->getCommision() ) * 1 );
				}
			} else {
				if( $this->_t()->isCommisionIncluded() )
				{
					$this->setBase( ($this->_t()->getValue() * $this->_t()->getLocalCurrencyRate() ) );
				} else {
					$this->setBase( ($this->_t()->getValue() + $this->_t()->getCommision() ) * $this->_t()->getLocalCurrencyRate() );
				}
			}
		}

	}
