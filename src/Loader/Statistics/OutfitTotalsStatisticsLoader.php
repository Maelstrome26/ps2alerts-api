<?php

namespace Ps2alerts\Api\Loader\Statistics;

use Ps2alerts\Api\Loader\Statistics\AbstractStatisticsLoader;
use Ps2alerts\Api\QueryObjects\QueryObject;
use Ps2alerts\Api\Repository\Statistics\OutfitTotalsRepository;

class OutfitTotalsStatisticsLoader extends AbstractStatisticsLoader
{
    /**
     * @var \Ps2alerts\Api\Repository\Metrics\OutfitTotalsRepository
     */
    protected $repository;

    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Repository\Metrics\OutfitTotalsRepository $repository
     */
    public function __construct(OutfitTotalsRepository $repository)
    {
        $this->repository = $repository;
        $this->setCacheNamespace('Statistics');
        $this->setType('OutfitTotals');
        $this->setFlags('outfitIDs');
    }
}
