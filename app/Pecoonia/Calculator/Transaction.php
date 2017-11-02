<?php
	namespace App\Pecoonia\Calculator;

	class Transaction implements TransactionContract {

		/**
		 * Tradevalue of the given transaction
		 * @var double
		 */
		protected $trade_value = 0;

		/**
		 * Book value of the given transaction
		 * @var double
		 */
		protected $book_value = 0;

		/**
		 * Commision of the given transaction, if any.
		 * @var double
		 */
		protected $commision = 0;

		/**
		 * The current quantity of the transaction
		 * @var int
		 */
		protected $quantity = 0;

		/**
		 * The quantity to use when calculating. 
		 * @var int
		 */
		protected $use_quantity = 0;

		/**
		 * The amount of days this security has been owned
		 * @var int
		 */
		protected $days_owned = 0;

		/**
		 * The local currency rate if any
		 * @var double
		 */
		protected $local_currency_rate = 0;

		/**
		 * The local book currency rate if any
		 * @var double
		 */
		protected $local_currency_rate_book_value = 0;

		/**
		 * Does this security use the same currency as the portfolio?
		 * @var boolean
		 */
		protected $is_same_currency = true;

		/**
		 * Does this security have commision included or excluded?
		 * @var boolean
		 */
		protected $is_commision_included = true;

		/**
		 * Is tax included?
		 * @var boolean
		 */
		protected $is_tax_included = false;

		/**
		 * the dividend
		 * @var double
		 */
		protected $dividend = 0;

		/**
		 * The dividend tax
		 * @var double
		 */
		protected $tax = 0;

		/**
		 * Constructor 
		 * @param array $prefill array with prefill method.
		 */
		public function __construct( $prefill = [] )
		{
			if( count($prefill) > 0 )
			{
				foreach( $prefill as $key => $value )
				{
					if( isset($this->{$key} ) )
						$this->{$key} = $value;
				}
			}
		}

		/**
		 * Get the boolean for commision included
		 * @param boolean $value the new value
		 */
		public function isTaxIncluded()
		{
			return $this->is_tax_included;
		}

		/**
		 * Set the boolean for commision included
		 * @param boolean $value the new value
		 */
		public function setIsTaxIncluded($value)
		{
			$this->is_tax_included = ( $value === false ? false : true );
			return $this;
		}

		/**
		 * Set the tax value
		 * @param boolean $value [description]
		 */
		public function setTax($value)
		{
			$this->tax = $value;
			return $this;
		}

		/**
		 * Get the tax value
		 * @return [type] [description]
		 */
		public function getTax()
		{
			return $this->tax;
		}

		/**
		 * Set the dividend
		 * @param boolean $value Amount
		 */
		public function setDividend($value)
		{
			$this->dividend = $value;
			return $this;
		}

		/**
		 * Get the dividend
		 * @return double returns the dividend or 0 if not set
		 */
		public function getDividend()
		{
			return $this->dividend;
		}

		/**
		 * Set the trade_value
		 * @param double $value the new trade value
		 */
		public function setValue($value)
		{
			$this->trade_value = $value;
			return $this;
		}

		/**
		 * Return the trade value
		 * @return double returns null if not set
		 */
		public function getValue()
		{
			return $this->trade_value;
		}

		/**
		 * Set the book_value
		 * @param double $value the new value
		 */
		public function setBookValue($value)
		{
			$this->book_value = $value;
			return $this;
		}

		/**
		 * Return the book value
		 * @return double 
		 */
		public function getBookValue()
		{
			return $this->book_value;
		}

		/**
		 * Set the commision
		 * @param double $value the new value
		 */
		public function setCommision($value)
		{
			$this->commision = $value;
			return $this;
		}

		/**
		 * Return the commision
		 * @return double 
		 */
		public function getCommision()
		{
			return $this->commision;
		}

		/**
		 * Set the quantity
		 * @param double $value the new value
		 */
		public function setQuantity($value)
		{
			$this->quantity = $value;
			return $this;
		}

		/**
		 * Return the quantity
		 * @return double 
		 */
		public function getQuantity()
		{
			return $this->quantity;
		}

		/**
		 * Set the use quantity
		 * @param double $value the new value
		 */
		public function setUseQuantity($value)
		{
			$this->use_quantity = $value;
			return $this;
		}

		/**
		 * Return the use quantity
		 * @return double 
		 */
		public function getUseQuantity()
		{
			return ( $this->use_quantity == 0 ? $this->quantity : $this->use_quantity );
		}

		/**
		 * Set the days owned
		 * @param double $value the new value
		 */
		public function setDaysOwned($value)
		{
			$this->days_owned = $value;
			return $this;
		}

		/**
		 * Return the days owned
		 * @return double 
		 */
		public function getDaysOwned()
		{
			return $this->days_owned;
		}

		/**
		 * Set the local currency rate
		 * @param double $value the new value
		 */
		public function setLocalCurrencyRate($value)
		{
			$this->local_currency_rate = $value;
			return $this;
		}

		/**
		 * Return the local currency rate
		 * @return double 
		 */
		public function getLocalCurrencyRate()
		{
			return $this->local_currency_rate;
		}

		/**
		 * Set the local currency rate for book value
		 * @param double $value the new value
		 */
		public function setLocalCurrencyRateBookValue($value)
		{
			$this->local_currency_rate_book_value = $value;
			return $this;
		}

		/**
		 * Return the local currency rate for book value
		 * @return double 
		 */
		public function getLocalCurrencyRateBookValue()
		{
			return $this->local_currency_rate_book_value;
		}

		/**
		 * Set the boolean for same currency
		 * @param boolean $value the new value
		 */
		public function setIsSameCurrency($value)
		{
			$this->is_same_currency = ( $value === false ? false : true );
			return $this;
		}

		/**
		 * Return the boolean for same currency
		 * @return boolean 
		 */
		public function isSameCurrency()
		{
			return $this->is_same_currency;
		}

		/**
		 * Set the boolean for commision included
		 * @param boolean $value the new value
		 */
		public function setIsCommisionIncluded($value)
		{
			$this->is_commision_included = ( $value === false ? false : true );
			return $this;
		}

		/**
		 * Return the boolean for commision included
		 * @return boolean 
		 */
		public function isCommisionIncluded()
		{
			return $this->is_commision_included;
		}

	}
