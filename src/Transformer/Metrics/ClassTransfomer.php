<?php

namespace Ps2alerts\Api\Transformer\Metrics;

use League\Fractal\TransformerAbstract;

class ClassTransformer extends TransformerAbstract
{
    /**
     * The transform method required by Fractal to parse the data and return proper typing and fields.
     *
     * @param  array $data Data to transform
     *
     * @return array
     */
    public function transform($data)
    {
        return [
            'id'        => (int) $data['dataID'],
            'alertID'   => (int) $data['resultID'],
            'timestamp' => (int) $data['timestamp'],
            'killsVS'   => (int) $data['killsVS'],
            'killsNC'   => (int) $data['killsVS'],
            'killsTR'   => (int) $data['killsTR']
        ];
    }
}
