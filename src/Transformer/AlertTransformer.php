<?php

namespace Ps2alerts\Api\Transformer;

use League\Fractal\TransformerAbstract;

class AlertTransformer extends TransformerAbstract
{
    public function transform($data)
    {
        return [
            'id' => (int) $data['ResultID'],
            'started' => (int) $data['ResultStartTime'],
            'ended' => (int) $data['ResultEndTime'],
            'endedOnDate' => (string) $data['ResultDateTime'],
            'server' => (int) $data['ResultServer'],
            'zone' => (int) $data['ResultAlertCont'],
            'winner' => (string) $data['ResultWinner'],
            'isDraw' => (boolean) $data['ResultDraw'],
            'isDomination' => (boolean) $data['ResultDomination'],
            'isValid' => (boolean) $data['Valid'],
            'inProgress' => (boolean) $data['InProgress']
        ];
    }
}
