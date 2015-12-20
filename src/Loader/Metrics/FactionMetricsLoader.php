<?php

namespace Ps2alerts\Api\Loader\Metrics;

use Ps2alerts\Api\Loader\Metrics\AbstractMetricsLoader;
use Ps2alerts\Api\Repository\Metrics\FactionRepository;

class FactionMetricsLoader extends AbstractMetricsLoader
{
    /**
     * @var \Ps2alerts\Api\Repository\Metrics\FactionRepository
     */
    protected $repository;

    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Repository\Metrics\FactionRepository $repository
     */
    public function __construct(FactionRepository $repository)
    {
        $this->repository = $repository;
        $this->setType('Factions');
        $this->setCacheNamespace('Metrics:');
    }
}
