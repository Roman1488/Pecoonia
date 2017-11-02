<?php
	namespace App\Pecoonia\Calculator\Type;

	/**
	 * Calculates the commision based on the given formulas
	 * 
	 */
	class Commision extends Base {

		// If s_currency is different from portfoliocurrency then (r_commision * 1)
		protected function calculateLocal()
		{
			if( $this->_t()->isSameCurrency() )
				$this->setLocal(0);
			else
				$this->setLocal( ($this->_t()->getCommision()*1) );
		}

		// if s_currency is equal to portfoliocurrency then (r_commision * 1)
		// if s_currency is different from portfoliocurrency then (r_commision * r_localcurrencyrate)
		protected function calculateBase()
		{
			if( $this->_t()->isSameCurrency() )
			{
				$this->setBase( ($this->_t()->getCommision()*1) );
			} else {
				$this->setBase( ( $this->_t()->getCommision() * $this->_t()->getLocalCurrencyRate() ) );
			}
		}
	}
