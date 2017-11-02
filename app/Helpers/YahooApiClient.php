<?php

namespace App\Helpers;

class YahooApiClient
{
    const UNAVIALABLE = 1;
    const EMPTY_RESULT = 2;
    const INVALID_RESULT = 3;

    /**
     * @var int $timeout
     */
    private $timeout;

    /**
     * ApiClient constructor.
     * @param int $timeout
     */
    public function __construct($timeout = 5)
    {
        $this->timeout = $timeout;
    }

    /**
     * Get quotes for one or multiple symbols
     *
     * @param array|string $pairs
     * @return array
     * @throws \Exception
     */
    public function getQuotes($pairs)
    {
        if (is_string($pairs))
        {
            $pairs = array($pairs);
        }
        $query = "select * from yahoo.finance.xchange where pair in ('".implode("','", $pairs)."')";
        return $this->execQuery($query);
    }

    /**
     * Execute the query
     * @param string $query
     * @return array
     * @throws \Exception
     */
    private function execQuery($query)
    {
        try
        {
            $url = $this->createUrl($query);
            $client = new HttpClient($url, $this->timeout);
            $response = $client->execute();
        }
        catch (\Exception $e)
        {
            throw new \Exception("Yahoo Finance API is not available.", $this::UNAVIALABLE, $e);
        }
        $decoded = json_decode($response, true);
        if (!isset($decoded['query']['results']) || count($decoded['query']['results']) === 0)
        {
            throw new \Exception("Yahoo Finance API did not return a result.", $this::EMPTY_RESULT);
        }
        return $decoded;
    }



    /**
     * Create the URL to call
     * @param array $query
     * @return string
     */
    private function createUrl($query)
    {
        $params = array(
            'env' => "store://datatables.org/alltableswithkeys",
            'format' => "json",
            'q' => $query,
        );
        return "https://query.yahooapis.com/v1/public/yql?".http_build_query($params);
    }
}