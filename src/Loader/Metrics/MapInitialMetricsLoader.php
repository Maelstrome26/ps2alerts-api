<?php

namespace Ps2alerts\Api\Loader\Metrics;

use Ps2alerts\Api\Loader\Metrics\AbstractMetricsLoader;
use Ps2alerts\Api\Repository\Metrics\MapInitialRepository;

class MapInitialMetricsLoader extends AbstractMetricsLoader
{
    /**
     * @var \Ps2alerts\Api\Repository\Metrics\MapInitialRepository
     */
    protected $repository;

    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Repository\Metrics\MapInitialRepository $repository
     */
    public function __construct(MapInitialRepository $repository)
    {
        $this->repository = $repository;
        $this->setType('MapInitial');
        $this->setCacheNamespace('Metrics:');
    }
}
