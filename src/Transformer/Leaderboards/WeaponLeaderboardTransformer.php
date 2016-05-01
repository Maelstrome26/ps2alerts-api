<?php

namespace Ps2alerts\Api\Transformer\Leaderboards;

use League\Fractal\TransformerAbstract;

class WeaponLeaderboardTransformer extends TransformerAbstract
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
            'kills'     => (int) $data['kills'],
            'teamkills' => (int) $data['teamkills'],
            'headshots' => (int) $data['headshots']
        ];
    }
}
