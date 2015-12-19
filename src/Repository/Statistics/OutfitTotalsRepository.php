<?php

namespace Ps2alerts\Api\Repository\Statistics;

use Ps2alerts\Api\Repository\AbstractEndpointRepository;

class OutfitTotalsRepository extends AbstractEndpointRepository
{
    /**
     * {@inheritdoc}
     */
    public function getTable()
    {
        return 'ws_outfits_total';
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey()
    {
        return 'outfitID';
    }

    /**
     * {@inheritdoc}
     */
    public function getResultKey()
    {
        return 'resultID';
    }
}
