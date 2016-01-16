<?php

namespace Ps2alerts\Api\Transformer\Metrics;

use League\Fractal\TransformerAbstract;

class CombatHistoryTransformer extends TransformerAbstract
{
    /**
     * The tranform method required by Fractal to parse the data and return proper typing and fields.
     *
     * @param  array $data Data to transform
     *
     * @return array
     */
    public function transform($data)
    {
        return [
            'alertID'   => (int) $data['resultID'],
            'timestamp' => (int) $data['timestamp'],
            'vs'   => (int) $data['killsVS'],
            'nc'   => (int) $data['killsVS'],
            'tr'   => (int) $data['killsTR']
        ];
    }
}
