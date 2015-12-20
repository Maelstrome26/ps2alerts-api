<?php

namespace Ps2alerts\Api\Loader\Statistics;

use Ps2alerts\Api\Loader\Statistics\AbstractStatisticsLoader;
use Ps2alerts\Api\QueryObjects\QueryObject;
use Ps2alerts\Api\Repository\AlertRepository;

class AlertStatisticsLoader extends AbstractStatisticsLoader
{
    /**
     * @var \Ps2alerts\Api\Repository\AlertRepository
     */
    protected $repository;

    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Repository\AlertRepository $repository
     */
    public function __construct(AlertRepository $repository)
    {
        $this->repository = $repository;
        $this->setCacheNamespace('Statistics');
        $this->setType('Alerts');
    }
}
