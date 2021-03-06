<?php

namespace Ps2alerts\Api\Repository\Metrics;

use Ps2alerts\Api\Repository\AbstractEndpointRepository;

class PlayerRepository extends AbstractEndpointRepository
{
    /**
     * {@inheritdoc}
     */
    public function getTable()
    {
        return 'ws_players';
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey()
    {
        return 'dataID';
    }

    /**
     * {@inheritdoc}
     */
    public function getResultKey()
    {
        return 'resultID';
    }
}
