<?php

namespace Ps2alerts\Api\Loader\Metrics;

use Ps2alerts\Api\Loader\Metrics\AbstractMetricsLoader;
use Ps2alerts\Api\Repository\Metrics\OutfitRepository;

class OutfitMetricsLoader extends AbstractMetricsLoader
{
    /**
     * @var \Ps2alerts\Api\Repository\Metrics\OutfitRepository
     */
    protected $repository;

    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Repository\Metrics\OutfitRepository $repository
     */
    public function __construct(OutfitRepository $repository)
    {
        $this->repository = $repository;
        $this->setType('Outfits');
        $this->setCacheNamespace('Metrics:');
    }
}
