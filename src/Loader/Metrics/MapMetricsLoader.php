<?php

namespace Ps2alerts\Api\Loader\Metrics;

use Ps2alerts\Api\Loader\Metrics\AbstractMetricsLoader;
use Ps2alerts\Api\Repository\Metrics\MapRepository;

class MapMetricsLoader extends AbstractMetricsLoader
{
    /**
     * @var \Ps2alerts\Api\Repository\Metrics\MapRepository
     */
    protected $repository;

    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Repository\Metrics\MapRepository $repository
     */
    public function __construct(MapRepository $repository)
    {
        $this->repository = $repository;
        $this->setCacheNamespace('Metrics:');
        $this->setType('Map');
    }
}
