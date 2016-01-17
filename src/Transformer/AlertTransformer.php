<?php

namespace Ps2alerts\Api\Transformer;

use League\Fractal\TransformerAbstract;
use Ps2alerts\Api\Repository\Metrics\ClassRepository;
use Ps2alerts\Api\Repository\Metrics\CombatHistoryRepository;
use Ps2alerts\Api\Repository\Metrics\FactionRepository;
use Ps2alerts\Api\Repository\Metrics\MapInitialRepository;
use Ps2alerts\Api\Repository\Metrics\MapRepository;
use Ps2alerts\Api\Repository\Metrics\OutfitRepository;
use Ps2alerts\Api\Repository\Metrics\PlayerRepository;
use Ps2alerts\Api\Repository\Metrics\PopulationRepository;
use Ps2alerts\Api\Repository\Metrics\VehicleRepository;
use Ps2alerts\Api\Repository\Metrics\WeaponRepository;
use Ps2alerts\Api\Repository\Metrics\XpRepository;
use Ps2alerts\Api\Transformer\AlertTransformer;
use Ps2alerts\Api\Transformer\Metrics\ClassTransformer;
use Ps2alerts\Api\Transformer\Metrics\CombatHistoryTransformer;
use Ps2alerts\Api\Transformer\Metrics\FactionTransformer;
use Ps2alerts\Api\Transformer\Metrics\MapInitialTransformer;
use Ps2alerts\Api\Transformer\Metrics\MapTransformer;
use Ps2alerts\Api\Transformer\Metrics\OutfitTransformer;
use Ps2alerts\Api\Transformer\Metrics\PlayerTransformer;
use Ps2alerts\Api\Transformer\Metrics\PopulationTransformer;
use Ps2alerts\Api\Transformer\Metrics\VehicleTransformer;
use Ps2alerts\Api\Transformer\Metrics\WeaponTransformer;
use Ps2alerts\Api\Transformer\Metrics\XpTransformer;

class AlertTransformer extends TransformerAbstract
{
    /**
     * List of available includes to this resource
     *
     * @var array
     */
    protected $availableIncludes = [
        'classes',
        'combatHistorys',
        'factions',
        'mapInitials',
        'maps',
        'outfits',
        'players',
        'populations',
        'vehicles',
        'weapons',
        'xps'
    ];

    /**
     * Repositories
     */
    protected $classRepo;
    protected $combatHistoryRepo;
    protected $factionRepo;
    protected $mapInitialRepo;
    protected $mapRepo;
    protected $outfitRepo;
    protected $playerRepo;
    protected $populationRepo;
    protected $vehicleRepo;
    protected $weaponRepo;
    protected $xpRepo;

    /**
     * Constructor
     *
     * @param ClassRepository         $classRepo
     * @param CombatHistoryRepository $combatHistoryRepo
     * @param FactionRepository       $factionRepo
     * @param MapInitialRepository    $mapInitialRepo
     * @param MapRepository           $mapRepo
     * @param OutfitRepository        $outfitRepo
     * @param PlayerRepository        $playerRepo
     * @param PopulationRepository    $populationRepo
     * @param VehicleRepository       $vehicleRepo
     * @param WeaponRepository        $weaponRepo
     * @param XpRepository            $xpRepo
     */
    public function __construct(
        ClassRepository         $classRepo,
        CombatHistoryRepository $combatHistoryRepo,
        FactionRepository       $factionRepo,
        MapInitialRepository    $mapInitialRepo,
        MapRepository           $mapRepo,
        OutfitRepository        $outfitRepo,
        PlayerRepository        $playerRepo,
        PopulationRepository    $populationRepo,
        VehicleRepository       $vehicleRepo,
        WeaponRepository        $weaponRepo,
        XpRepository            $xpRepo
    ) {
        $this->classRepo         = $classRepo;
        $this->combatHistoryRepo = $combatHistoryRepo;
        $this->factionRepo       = $factionRepo;
        $this->mapInitialRepo    = $mapInitialRepo;
        $this->mapRepo           = $mapRepo;
        $this->outfitRepo        = $outfitRepo;
        $this->playerRepo        = $playerRepo;
        $this->populationRepo    = $populationRepo;
        $this->vehicleRepo       = $vehicleRepo;
        $this->weaponRepo        = $weaponRepo;
        $this->xpRepo            = $xpRepo;
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
     * Gets the Class data and then adds it to the result
     *
     * @param  array $data
     *
     * @return League\Fractal\Resource\Collection
     */
    public function includeClasses($data)
    {
        $data = $this->classRepo->readAll($data['ResultID'], 'result');
        return $this->collection($data, new ClassTransformer);
    }

    /**
     * Gets the Combat History data and then adds it to the result
     *
     * @param  array $data
     *
     * @return League\Fractal\Resource\Collection
     */
    public function includeCombatHistorys($data)
    {
        $data = $this->combatHistoryRepo->readAll($data['ResultID'], 'result');
        return $this->collection($data, new CombatHistoryTransformer);
    }

    /**
     * Gets the Faction data and then adds it to the result
     *
     * @param  array $data
     *
     * @return League\Fractal\Resource\Collection
     */
    public function includeFactions($data)
    {
        $data = $this->factionRepo->readAll($data['ResultID'], 'result');
        return $this->item($data, new FactionTransformer);
    }

    /**
     * Gets the Class data and then adds it to the result
     *
     * @param  array $data
     *
     * @return League\Fractal\Resource\Collection
     */
    public function includeMapInitials($data)
    {
        $data = $this->mapInitialRepo->readAll($data['ResultID'], 'result');
        return $this->collection($data, new MapInitialTransformer);
    }

    /**
     * Gets the Map data and then adds it to the result
     *
     * @param  array $data
     *
     * @return League\Fractal\Resource\Collection
     */
    public function includeMaps($data)
    {
        $data = $this->mapRepo->readAll($data['ResultID'], 'result');
        return $this->collection($data, new MapTransformer);
    }

    /**
     * Gets the Outfit data and then adds it to the result
     *
     * @param  array $data
     *
     * @return League\Fractal\Resource\Collection
     */
    public function includeOutfits($data)
    {
        $data = $this->outfitRepo->readAll($data['ResultID'], 'result');
        return $this->collection($data, new OutfitTransformer);
    }

    /**
     * Gets the Popualtion data and then adds it to the result
     *
     * @param  array $data
     *
     * @return League\Fractal\Resource\Collection
     */
    public function includePopulations($data)
    {
        $data = $this->populationRepo->readAll($data['ResultID'], 'result');
        return $this->collection($data, new PopulationTransformer);
    }

    /**
     * Gets the Player data and then adds it to the result
     *
     * @param  array $data
     *
     * @return League\Fractal\Resource\Collection
     */
    public function includePlayers($data)
    {
        $data = $this->playerRepo->readAll($data['ResultID'], 'result');
        return $this->collection($data, new PlayerTransformer);
    }

    /**
     * Gets the Vehicle data and then adds it to the result
     *
     * @param  array $data
     *
     * @return League\Fractal\Resource\Collection
     */
    public function includeVehicles($data)
    {
        $data = $this->vehicleRepo->readAll($data['ResultID'], 'result');
        return $this->collection($data, new VehicleTransformer);
    }

    /**
     * Gets the Weapon data and then adds it to the result
     *
     * @param  array $data
     *
     * @return League\Fractal\Resource\Collection
     */
    public function includeWeapons($data)
    {
        $map = $this->weaponRepo->readAll($data['ResultID'], 'result');
        return $this->collection($map, new WeaponTransformer);
    }

    /**
     * Gets the XP data and then adds it to the result
     *
     * @param  array $data
     *
     * @return League\Fractal\Resource\Collection
     */
    public function includeXps($data)
    {
        // NOTE TO SELF: RATE LIMIT THIS BAD BOY
        $map = $this->xpRepo->readAll($data['ResultID'], 'result');
        return $this->collection($map, new XpTransformer);
    }
}
