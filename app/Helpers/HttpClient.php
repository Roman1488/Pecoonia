<?php
namespace App\Helpers;

class HttpClient
{

    /**
     * @var string $url
     */
    private $url;

    /**
     * @var int $timeout
     */
    private $timeout;


    /**
     * Init with URL
     * @param string $url
     * @param int $timeout
     */
    public function __construct($url, $timeout)
    {
        $this->url = $url;
        $this->timeout = $timeout;
    }


    /**
     * Execute the HTTP query
     * @return string
     * @throws \Exception
     */
    public function execute()
    {
        $ch = curl_init($this->url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
        ));

        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpStatus !== 200)
        {
            throw new \Exception("HTTP call failed with error ".$httpStatus.".", $httpStatus);
        }
        elseif ($response === false)
        {
            throw new \Exception("HTTP call failed empty response.", 0);
        }

        return $response;
    }

}
