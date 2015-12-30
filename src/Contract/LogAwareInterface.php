<?php

namespace Ps2alerts\Api\Contract;

use Monolog\Logger;

interface LogAwareInterface
{
    /**
     * Sets the Log
     *
     * @param \Monolog\Logger
     */
    public function setLogDriver(Logger $logger);

    /**
     * Gets the Log
     *
     * @return \Monolog\Logger
     */
    public function getLogDriver();
}
