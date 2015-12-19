<?php

namespace Ps2alerts\Api\Loader\Metrics;

use Ps2alerts\Api\Loader\Metrics\AbstractMetricsLoader;
use Ps2alerts\Api\Repository\Metrics\OutfitRepository;

class OutfitMetricsLoader extends AbstractMetricsLoader
{
    protected $repository;

    public function __construct(OutfitRepository $repository)
    {
        $this->repository = $repository;
        $this->setCacheNamespace('Metrics:');
        $this->setType('Outfits');
    }
}
