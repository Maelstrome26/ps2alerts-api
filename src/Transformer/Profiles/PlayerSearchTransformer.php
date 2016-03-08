<?php

namespace Ps2alerts\Api\Transformer\Profiles;

use League\Fractal\TransformerAbstract;

class PlayerSearchTransformer extends TransformerAbstract
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
            'id'      => (string) $data['playerID'], // Bigint
            'name'    => (string) $data['playerName'],
            'faction' => (int) $data['playerFaction']
        ];
    }
}
