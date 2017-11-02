<?php
	namespace App\Pecoonia\Calculator\Type;

	/**
	 * Calculate the trade quote commision
	 */
	class TradeQuoteCommision extends Base {

		protected function calculateLocal()
		{
			if( $this->_t()->isSameCurrency() )
			{
				$this->setLocal(0);
			} else {
				if( $this->_t()->isCommisionIncluded() )
				{
					$this->setLocal( ( ( $this->_t()->getValue() - $this->_t()->getCommision() ) * 1 ) / $this->_t()->getQuantity() );
				} else {
					$this->setLocal( ($this->_t()->getValue() * 1) / $this->_t()->getQuantity() );
				}
			}
		}

		protected function calculateBase()
		{
			if( $this->_t()->isSameCurrency() )
			{
				if( $this->_t()->isCommisionIncluded() )
				{
					$this->setBase(  (($this->_t()->getValue()-$this->_t()->getCommision())*1) / $this->_t()->getQuantity()  );
				} else {
					$this->setBase( ($this->_t()->getValue()*1) / $this->_t()->getQuantity() );
				}
			} else {
				if( $this->_t()->isCommisionIncluded() )
				{
					$this->setBase( (($this->_t()->getValue()-$this->_t()->getCommision())*$this->_t()->getLocalCurrencyRate()) / $this->_t()->getQuantity() );
				} else {
					$this->setBase( ($this->_t()->getValue() * $this->_t()->getLocalCurrencyRate()) / $this->_t()->getQuantity() );
				}	
			}
		}

	}
