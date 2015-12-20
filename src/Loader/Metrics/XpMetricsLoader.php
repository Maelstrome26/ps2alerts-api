<?php

namespace Ps2alerts\Api\Loader\Metrics;

use Ps2alerts\Api\Loader\Metrics\AbstractMetricsLoader;
use Ps2alerts\Api\Repository\Metrics\XpRepository;

class XpMetricsLoader extends AbstractMetricsLoader
{
    /**
     * @var \Ps2alerts\Api\Repository\Metrics\XpRepository
     */
    protected $repository;

    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Repository\Metrics\XpRepository $repository
     */
    public function __construct(XpRepository $repository)
    {
        $this->repository = $repository;
        $this->setType('XP');
        $this->setCacheNamespace('Metrics:');
    }
}
