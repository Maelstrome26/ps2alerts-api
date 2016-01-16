<?php

namespace Ps2alerts\Api\Transformer;

use League\Fractal\TransformerAbstract;
use Ps2alerts\Api\Repository\Metrics\CombatHistoryRepository;
use Ps2alerts\Api\Repository\Metrics\MapRepository;
use Ps2alerts\Api\Transformer\Metric\CombatHistoryTransformer;
use Ps2alerts\Api\Transformer\Metric\MapTransformer;

class AlertTransformer extends TransformerAbstract
{
    /**
     * List of available includes to this resource
     *
     * @var array
     */
    protected $availableIncludes = [
        'combatHistory',
        'map'
    ];

    /**
     * Repositories
     */
    protected $combatHistoryRepo;
    protected $mapRepo;

    public function __construct(
        CombatHistoryRepository $combatHistoryRepo,
        MapRepository           $mapRepo
    ) {
        $this->combatHistoryRepo = $combatHistoryRepo;
        $this->mapRepo           = $mapRepo;
    }

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

    /**
     * Gets the Combat History data and then adds it to the result
     *
     * @param  array $data
     *
     * @return League\Fractal\Resource\Collection
     */
    public function includeCombatHistory($data)
    {
        $map = $this->combatHistoryRepo->readAll($data['ResultID'], 'result');
        return $this->collection($map, new CombatHistoryTransformer);
    }

    /**
     * Gets the Map data and then adds it to the result
     *
     * @param  array $data
     *
     * @return League\Fractal\Resource\Collection
     */
    public function includeMap($data)
    {
        $map = $this->mapRepo->readAll($data['ResultID'], 'result');
        return $this->collection($map, new MapTransformer);
    }
}
