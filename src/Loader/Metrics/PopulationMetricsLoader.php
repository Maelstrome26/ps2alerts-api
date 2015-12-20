<?php

namespace Ps2alerts\Api\Loader\Metrics;

use Ps2alerts\Api\Loader\Metrics\AbstractMetricsLoader;
use Ps2alerts\Api\Repository\Metrics\PopulationRepository;

class PopulationMetricsLoader extends AbstractMetricsLoader
{
    /**
     * @var \Ps2alerts\Api\Repository\Metrics\PopulationRepository
     */
    protected $repository;

    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Repository\Metrics\PopulationRepository $repository
     */
    public function __construct(PopulationRepository $repository)
    {
        $this->repository = $repository;
        $this->setType('Population');
        $this->setCacheNamespace('Metrics:');
    }
}
