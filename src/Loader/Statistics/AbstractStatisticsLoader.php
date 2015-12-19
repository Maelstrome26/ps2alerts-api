<?php

namespace Ps2alerts\Api\Loader\Statistics;

use Ps2alerts\Api\Loader\AbstractLoader;
use Ps2alerts\Api\QueryObjects\QueryObject;

abstract class AbstractStatisticsLoader extends AbstractLoader
{
    /**
     * @var string
     */
    protected $type;

    /**
     * Sets the type of statistics we're looking for
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Returns the type of statistics
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Construct setting cache namespace
     */
    public function __construct()
    {
        $this->setCacheNamespace('Statistics:');
    }

    public function top()
    {

    }
}
