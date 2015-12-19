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
     * Construct setting cache namespace
     */
    public function __construct()
    {
        $this->setCacheNamespace('Statistics:');
    }
}
