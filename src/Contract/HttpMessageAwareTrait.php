<?php

namespace Ps2alerts\Api\Contract;

use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response;

trait HttpMessageAwareTrait
{
    /**
     * @var \Zend\Diactoros\ServerRequestFactory
     */
    protected $request;

    /**
     * @var \Zend\Diactoros\Response
     */
    protected $response;

    /**
     * Set the http message request
     *
     * @param \Zend\Diactoros\ServerRequestFactory $obj
     */
    public function setRequest(ServerRequest $obj)
    {
        $this->request = $obj;
    }

    /**
     * Getthe http message request
     *
     * @param \Zend\Diactoros\ServerRequestFactory $obj
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Sets the http response object
     *
     * @param Zend\Diactoros\Response
     */
    public function setResponse(Response $obj)
    {
        $this->response = $obj;
    }

    /**
     * Gets the http response object
     *
     * @return Zend\Diactoros\Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
