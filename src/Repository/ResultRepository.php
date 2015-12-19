<?php

namespace Ps2alerts\Api\Repository;

use Ps2alerts\Api\Repository\AbstractEndpointRepository;

class ResultRepository extends AbstractEndpointRepository
{
    /**
     * {@inheritdoc}
     */
    public function getTable()
    {
        return 'ws_results';
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey()
    {
        return 'ResultID';
    }

    /**
     * {@inheritdoc}
     */
    public function getResultKey()
    {
        return $this->getPrimaryKey();
    }
}
