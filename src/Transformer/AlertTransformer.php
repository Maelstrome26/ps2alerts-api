<?php

namespace Ps2alerts\Api\Transformer;

use League\Fractal\TransformerAbstract;

class AlertTransformer extends TransformerAbstract
{
    /**
     * List of available embeds to this resource
     *
     * @var array
     */
    protected $availableEmbeds = [
        'map'
    ];

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
            'id'           => (int) $data['ResultID'],
            'started'      => (int) $data['ResultStartTime'],
            'ended'        => (int) $data['ResultEndTime'],
            'server'       => (int) $data['ResultServer'],
            'zone'         => (int) $data['ResultAlertCont'],
            'winner'       => (string) $data['ResultWinner'],
            'isDraw'       => (boolean) $data['ResultDraw'],
            'isDomination' => (boolean) $data['ResultDomination'],
            'isValid'      => (boolean) $data['Valid'],
            'inProgress'   => (boolean) $data['InProgress']
        ];
    }
}
