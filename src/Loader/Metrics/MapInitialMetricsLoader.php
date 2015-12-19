<?php

namespace Ps2alerts\Api\Loader\Metrics;

use Ps2alerts\Api\Loader\Metrics\AbstractMetricsLoader;
use Ps2alerts\Api\Repository\Metrics\MapInitialRepository;

class MapInitialMetricsLoader extends AbstractMetricsLoader
{
    protected $repository;

    public function __construct(MapInitialRepository $repository)
    {
        $this->repository = $repository;
        $this->setCacheNamespace('Metrics:');
        $this->setType('MapInitial');
    }
}
