<?php
	namespace App\Pecoonia\Calculator\Type;

	class Base {

		// Calculated
		private $localValue = null;
		private $baseValue = null;
		private $value = null;

		private $_transaction = null;
		private $_sigma_transactions = [];
		private $_caller;

		public function __construct(\App\Pecoonia\Calculator\TransactionCalculator &$caller, &$transaction, &$sigma_transactions = [])
		{
			$this->_transaction = $transaction;
			$this->_sigma_transactions = $sigma_transactions;
			$this->_caller = $caller;
		}

		protected function calculator()
		{
			return $this->_caller;
		}

		protected function _t()
		{
			return $this->_transaction;
		}

		protected function _e( $function )
		{
			$addition = 0;

			foreach( $this->_sigma_transactions as $t )
			{
				$addition += $function($t);
			}

			return $addition;
		}

		protected function calculateLocal()
		{
			return 0;
		}

		protected function calculateBase()
		{
			return 0;
		}

		public function getLocal()
		{
			if( is_null($this->localValue ) )
			{
				$this->calculateLocal();
			}

			return $this->localValue;
		}

		public function getBase()
		{
			if( is_null($this->baseValue ) )
			{
				$this->calculateBase();
			}

			return $this->baseValue;
		}

		public function getValue()
		{
			if( is_null( $this->value ) )
			{
				$this->calculateValue();
			}

			return $this->value;
		}

		protected function setLocal($value)
		{
			$this->localValue = $value;
			return $this;
		}

		protected function setBase($value)
		{
			$this->baseValue = $value;
			return $this;
		}

		protected function setValue($value)
		{
			$this->value = $value;
			return $this;
		}

	}
