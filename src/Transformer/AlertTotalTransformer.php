<?php

namespace Ps2alerts\Api\Transformer;

use League\Fractal\TransformerAbstract;

class AlertTotalTransformer extends TransformerAbstract
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
            'total'       => (int) $data['total'],
            'dominations' => (int) $data['dominations'],
            'vs'          => (int) $data['vs'],
            'nc'          => (int) $data['nc'],
            'tr'          => (int) $data['tr'],
            'draw'        => (int) $data['draw']
        ];
    }
}
