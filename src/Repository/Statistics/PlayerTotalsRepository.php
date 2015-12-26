<?php

namespace Ps2alerts\Api\Repository\Statistics;

use Ps2alerts\Api\Repository\AbstractEndpointRepository;

class PlayerTotalsRepository extends AbstractEndpointRepository
{
    /**
     * {@inheritdoc}
     */
    public function getTable()
    {
        return 'ws_players_total';
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey()
    {
        return 'playerID';
    }

    /**
     * {@inheritdoc}
     */
    public function getResultKey()
    {
        return null;
    }
}
