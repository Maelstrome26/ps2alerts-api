<?php

namespace Ps2alerts\Api\Loader\Statistics;

use Ps2alerts\Api\Loader\Statistics\AbstractStatisticsLoader;
use Ps2alerts\Api\Repository\Statistics\OutfitTotalsRepository;

class OutfitTotalsMetricsLoader extends AbstractStatisticsLoader
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
        $this->setType('OutfitTotals');
    }

    /**
     * Pulls the top X of the statistic
     *
     * @param  integer $length Number of top items to return
     *
     * @return array
     */
    public function readTop($length = 10)
    {
        $this->setType("OutfitTotals:Top{$length}");

        
    }
}
