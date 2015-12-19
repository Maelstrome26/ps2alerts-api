<?php

namespace Ps2alerts\Api\Loader\Metrics;

use Ps2alerts\Api\Loader\Metrics\AbstractMetricsLoader;
use Ps2alerts\Api\Repository\Metrics\MapRepository;

class MapMetricsLoader extends AbstractMetricsLoader
{
    protected $repository;

    public function __construct(MapRepository $repository)
    {
        $this->repository = $repository;
        $this->setType('Map');
    }
}
