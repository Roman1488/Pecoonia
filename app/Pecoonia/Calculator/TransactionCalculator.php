<?php
	namespace App\Pecoonia\Calculator;

	class TransactionCalculator
	{
		private $_type_namespace = "\\".__NAMESPACE__ ."\\Type\\";

		private $_firstTransaction;
		private $_fifoTransactions = [];
		private $_calculations = [];

		public function __construct( $firstTransaction, $fifoTransactions = [] )
		{
			$this->_firstTransaction = $firstTransaction;
			$this->_fifoTransactions = $fifoTransactions;
		}

	   	public function __get($key) {

	   		$realKey = ucfirst($key);

	    	if( !isset( $this->_calculations[$realKey] ) )
	    	{
	    		$calcClass = $this->_type_namespace . $realKey;

	    		try {
	    			$calc = new $calcClass($this, $this->_firstTransaction, $this->_fifoTransactions);
	    			$this->_calculations[$realKey] = $calc;
	    		} catch(\Exception $e)
	    		{
	    			$this->_calculations[$realKey] = new EmptyResult();
	    		}
	    		
	    	}

	    	return $this->_calculations[$realKey];
	   	}
	}
