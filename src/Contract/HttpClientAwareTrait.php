<?php

namespace Ps2alerts\Api\Contract;

use GuzzleHttp\Client;

trait HttpClientAwareTrait
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * Set the http client
     *
     * @param \GuzzleHttp\Client $httpClient
     */
    public function setHttpClientDriver(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Get the http client
     *
     * @return \GuzzleHttp\Client
     */
    public function getHttpClientDriver()
    {
        return $this->httpClient;
    }
}
