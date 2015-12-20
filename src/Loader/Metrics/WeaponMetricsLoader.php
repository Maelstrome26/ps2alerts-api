<?php

namespace Ps2alerts\Api\Loader\Metrics;

use Ps2alerts\Api\Loader\Metrics\AbstractMetricsLoader;
use Ps2alerts\Api\Repository\Metrics\WeaponRepository;

class WeaponMetricsLoader extends AbstractMetricsLoader
{
    /**
     * @var \Ps2alerts\Api\Repository\Metrics\WeaponRepository
     */
    protected $repository;

    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Repository\Metrics\WeaponRepository $repository
     */
    public function __construct(WeaponRepository $repository)
    {
        $this->repository = $repository;
        $this->setType('Weapons');
        $this->setCacheNamespace('Metrics:');
    }
}
