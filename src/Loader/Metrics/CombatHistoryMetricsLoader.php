<?php

namespace Ps2alerts\Api\Loader\Metrics;

use Ps2alerts\Api\Loader\Metrics\AbstractMetricsLoader;
use Ps2alerts\Api\Repository\Metrics\CombatHistoryRepository;

class CombatHistoryMetricsLoader extends AbstractMetricsLoader
{
    /**
     * @var \Ps2alerts\Api\Repository\Metrics\CombatHistoryRepository
     */
    protected $repository;

    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Repository\Metrics\CombatHistoryRepository $repository
     */
    public function __construct(CombatHistoryRepository $repository)
    {
        $this->repository = $repository;
        $this->setType('CombatHistory');
        $this->setCacheNamespace('Metrics:');
    }
}
