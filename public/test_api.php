<?php
	if( isset($_GET["symbol"] ) )
	{
		$s = $_GET["symbol"];

		$data = file_get_contents( "https://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yahoo.finance.quotes%20where%20symbol%20in%20(%22" . $s . "%22)&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=" );
		$dj = json_decode( $data, true );

		print "<pre>";
			print_r( $dj["query"]["results"] );
		print "</pre>";
	} else {
		print "Nope.";
	}