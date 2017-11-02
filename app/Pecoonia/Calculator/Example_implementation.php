<?php
	namespace App\Pecoonia\Calculator;

	// Simple requires, should not be neccesary with autoloaders.
	require_once("TransactionContract.php");
	require_once("Transaction.php");
	require_once("TransactionCalculator.php");
	require_once("Type/Base.php");

	$type_directory = dirname(__FILE__) . "/Type";

	foreach (scandir($type_directory) as $filename) {
		if( $filename == "Base.php" )
			continue;

	    $path = $type_directory . '/' . $filename;
	    if (is_file($path)) {
	        require $path;
	    }
	}

	// Prefill array for transaction class.
	$firstTransaction = 
	[
		"trade_value" => 8612.00,
		"book_value" => 8612.00,
		"commision" => 12,
		"is_commision_included" => true,
		"is_same_currency" => false,
		"local_currency_rate" => 6.5634,
		"local_currency_rate_book_value" => 6.5634,
		"quantity" => 200,
		"days_owned" => 20
	];

	// Prefill array for transaction class.
	$secondTransaction = 
	[
		"trade_value" => 9512.00,
		"book_value" => 9512.00,
		"commision" => 12,
		"is_commision_included" => true,
		"is_same_currency" => false,
		"local_currency_rate" => 6.5978,
		"local_currency_rate_book_value" => 6.5978,
		"quantity" => 300,
		"use_quantity" => 200,
		"days_owned" => 10
	];

	// Prefill array for transaction class.
	$sellTransaction = 
	[
		"trade_value" => 20012.00,
		"book_value" => 20012.00,
		"commision" => 12,
		"is_commision_included" => true,
		"is_same_currency" => false,
		"local_currency_rate" => 6.6213,
		"local_currency_rate_book_value" => 6.6213,
		"quantity" => 400,
		"dividend" => 100,
		"tax" => 100,
		"is_tax_included" => false
	];



	// Lets first create our transaction objects.
	$securitySellTransaction = new Transaction( $sellTransaction );
	$fifoBuyTransactions = [ new Transaction( $firstTransaction ), new Transaction($secondTransaction) ];

	// Lets put the calculator to work. 
	$calculator = new TransactionCalculator( $securitySellTransaction, $fifoBuyTransactions );

	print "<pre>";
		print "<b>Commision: <br/></b>";
		print "Local: " .$calculator->Commision->getLocal() . "<br>";
		print "Base: " . $calculator->Commision->getBase() . "<br>";
	print "</pre>";

	print "<pre>";
		print "<b>Trade value: <br/></b>";
		print "Local: " .$calculator->TradeValue->getLocal() . "<br>";
		print "Base: " . $calculator->TradeValue->getBase() . "<br>";
	print "</pre>";

	print "<pre>";
		print "<b>Trade quote: <br/></b>";
		print "Local: " .$calculator->TradeQuote->getLocal() . "<br>";
		print "Base: " . $calculator->TradeQuote->getBase() . "<br>";
	print "</pre>";

	print "<pre>";
		print "<b>Trade quote pre commision: <br/></b>";
		print "Local: " .$calculator->TradeQuoteCommision->getLocal() . "<br>";
		print "Base: " . $calculator->TradeQuoteCommision->getBase() . "<br>";
	print "</pre>";

	print "<pre>";
		print "<b>Purchase value: <br/></b>";
		print "Local: " .$calculator->PurchaseValue->getLocal() . "<br>";
		print "Base: " . $calculator->PurchaseValue->getBase() . "<br>";
	print "</pre>";

	print "<pre>";
		print "<b>Bookvalue: <br/></b>";
		print "Local: " .$calculator->BookValue->getLocal() . "<br>";
		print "Base: " . $calculator->BookValue->getBase() . "<br>";
	print "</pre>";

	print "<pre>";
		print "<b>Purchase price: <br/></b>";
		print "Local: " .$calculator->PurchasePrice->getLocal() . "<br>";
		print "Base: " . $calculator->PurchasePrice->getBase() . "<br>";
	print "</pre>";

	print "<pre>";
		print "<b>Book price: <br/></b>";
		print "Local: " .$calculator->BookPrice->getLocal() . "<br>";
		print "Base: " . $calculator->BookPrice->getBase() . "<br>";
	print "</pre>";

	print "<pre>";
		print "<b>Purchase currency rate: <br/></b>";
		print "Value: " .$calculator->PurchaseCurrencyRate->getValue() . "<br>";
	print "</pre>";

	print "<pre>";
		print "<b>Book currency rate: <br/></b>";
		print "Value: " .$calculator->BookCurrencyRate->getValue() . "<br>";
	print "</pre>";

	print "<pre>";
		print "<b>Profit loss book value: <br/></b>";
		print "Local: " .$calculator->ProfitLossBookValue->getLocal() . "<br>";
		print "Base: " . $calculator->ProfitLossBookValue->getBase() . "<br>";
	print "</pre>";

	print "<pre>";
		print "<b>Profit loss purchase value: <br/></b>";
		print "Local: " .$calculator->ProfitLossPurchaseValue->getLocal() . "<br>";
		print "Base: " . $calculator->ProfitLossPurchaseValue->getBase() . "<br>";
	print "</pre>";

	print "<pre>";
		print "<b>Profit loss purchase value LC Account: <br/></b>";
		print "Value: " .$calculator->ProfitLossPurchaseValueLCAccount->getValue() . "<br>";
	print "</pre>";

	print "<pre>";
		print "<b>Return purchase value: <br/></b>";
		print "Local: " .$calculator->ReturnPurchaseValue->getLocal() . "<br>";
		print "Base: " . $calculator->ReturnPurchaseValue->getBase() . "<br>";
	print "</pre>";

	print "<pre>";
		print "<b>Return book value: <br/></b>";
		print "Local: " .$calculator->ReturnBookValue->getLocal() . "<br>";
		print "Base: " . $calculator->ReturnBookValue->getBase() . "<br>";
	print "</pre>";

	print "<pre>";
		print "<b>Currency profit loss book value: <br/></b>";
		print "Value: " .$calculator->CurrencyProfitLossBookValue->getValue() . "<br>";
	print "</pre>";

	print "<pre>";
		print "<b>Currency profit loss purchase price: <br/></b>";
		print "Value: " .$calculator->CurrencyProfitLossPurchasePrice->getValue() . "<br>";
	print "</pre>";

	print "<pre>";
		print "<b>Profit loss book value excurrency: <br/></b>";
		print "Value: " .$calculator->ProfitLossBookValueExcurrency->getValue() . "<br>";
	print "</pre>";

	print "<pre>";
		print "<b>Profit loss purchase price excurrency: <br/></b>";
		print "Value: " .$calculator->ProfitLossPurchasePriceExcurrency->getValue() . "<br>";
	print "</pre>";

	print "<pre>";
		print "<b>Average purchase price: <br/></b>";
		print "Value: " .$calculator->AveragePurchasePrice->getValue() . "<br>";
	print "</pre>";

	print "<pre>";
		print "<b>Average profit loss purchase price: <br/></b>";
		print "Value: " .$calculator->AverageProfitLossPurchasePrice->getValue() . "<br>";
	print "</pre>";

	print "<pre>";
		print "<b>Average days owned: <br/></b>";
		print "Value: " .$calculator->AverageDaysOwned->getValue() . "<br>";
	print "</pre>";

	print "<pre>";
		print "<b>Daily profit loss purchase value: <br/></b>";
		print "Local: " .$calculator->DailyProfitLossPurchaseValue->getLocal() . "<br>";
		print "Base: " . $calculator->DailyProfitLossPurchaseValue->getBase() . "<br>";
	print "</pre>";
	
	print "<pre>";
		print "<b>Daily return purchase value: <br/></b>";
		print "Local: " .$calculator->DailyReturnPurchaseValue->getLocal() . "<br>";
		print "Base: " . $calculator->DailyReturnPurchaseValue->getBase() . "<br>";
	print "</pre>";

	print "<pre>";
		print "<b>Annualized return purchase value: <br/></b>";
		print "Local: " .$calculator->AnnualizedReturnPurchaseValue->getLocal() . "<br>";
		print "Base: " . $calculator->AnnualizedReturnPurchaseValue->getBase() . "<br>";
	print "</pre>";

print "<hr>";

	print "<pre>";
		print "<b>Dividend: <br/></b>";
		print "Local: " .$calculator->Dividend->getLocal() . "<br>";
		print "Base: " . $calculator->Dividend->getBase() . "<br>";
	print "</pre>";

	print "<pre>";
		print "<b>Net dividend: <br/></b>";
		print "Local: " .$calculator->NetDividend->getLocal() . "<br>";
		print "Base: " . $calculator->NetDividend->getBase() . "<br>";
	print "</pre>";

	print "<pre>";
		print "<b>Tax: <br/></b>";
		print "Local: " .$calculator->Tax->getLocal() . "<br>";
		print "Base: " . $calculator->Tax->getBase() . "<br>";
	print "</pre>";
