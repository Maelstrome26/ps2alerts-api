<?php

namespace Ps2alerts\Api\Loader\Metrics;

use Ps2alerts\Api\Loader\Metrics\AbstractMetricsLoader;
use Ps2alerts\Api\Repository\Metrics\VehicleRepository;

class VehicleMetricsLoader extends AbstractMetricsLoader
{
    /**
     * @var \Ps2alerts\Api\Repository\Metrics\VehicleRepository
     */
    protected $repository;

    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Repository\Metrics\VehicleRepository $repository
     */
    public function __construct(VehicleRepository $repository)
    {
        $this->repository = $repository;
        $this->setType('Vehicle');
        $this->setCacheNamespace('Metrics:');
    }
}
