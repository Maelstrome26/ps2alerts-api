<?php

namespace Ps2alerts\Api\Contract;

use Monolog\Logger;

trait LogAwareTrait
{
    /**
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * Set the Log driver
     *
     * @param \Monolog\Logger $logger
     */
    public function setLogDriver(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Get the Log driver
     *
     * @return \Monolog\Logger
     */
    public function getLogDriver()
    {
        return $this->logger;
    }
}
