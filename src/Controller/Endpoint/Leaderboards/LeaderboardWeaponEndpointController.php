<?php

namespace Ps2alerts\Api\Controller\Endpoint\Leaderboards;

use League\Fractal\Manager;
use Ps2alerts\Api\Controller\Endpoint\AbstractEndpointController;
use Ps2alerts\Api\Controller\Endpoint\Leaderboards\AbstractLeaderboardEndpointController;
use Ps2alerts\Api\Exception\CensusEmptyException;
use Ps2alerts\Api\Exception\CensusErrorException;
use Ps2alerts\Api\Repository\Metrics\WeaponTotalRepository;
use Ps2alerts\Api\Transformer\Leaderboards\WeaponLeaderboardTransformer;

class LeaderboardWeaponEndpointController extends AbstractLeaderboardEndpointController
{
    protected $repository;

    /**
     * Construct
     *
     * @param League\Fractal\Manager $fractal
     */
    public function __construct(
        Manager                $fractal,
        WeaponTotalRepository  $repository
    ) {

        $this->fractal = $fractal;
        $this->repository = $repository;
    }

    /**
     * Get Weapon Leaderboard
     *
     * @return \League\Fractal\Manager
     */
    public function weapons()
    {
        $valid = $this->validateRequestVars();

        // If validation didn't pass, chuck 'em out
        if ($valid !== true) {
            return $this->errorWrongArgs($valid->getMessage());
        }

        // Translate field into table specific columns
        if (isset($_GET['field'])) {
            $field = $this->getField($_GET['field']);
        }

        if (! isset($field)) {
            return $this->errorWrongArgs('Field wasn\'t provided and is required.');
        }

        $weapons = $this->checkRedis('api', 'leaderboards', "weapons:{$field}");

        // If we have this cached already
        if (empty($weapons)) {
            // Perform Query
            $query = $this->repository->newQuery();
            $query->cols([
                'weaponID',
                'SUM(killCount) as killCount',
                'SUM(teamkills) as teamkills',
                'SUM(headshots) as headshots'
            ]);
            $query->where('weaponID > 0');
            $query->orderBy(["{$field} desc"]);
            $query->groupBy(['weaponID']);

            $weapons = $this->repository->fireStatementAndReturn($query);

            // Cache results in redis
            $this->storeInRedis('api', 'leaderboards', "weapons:{$field}", $weapons);
        }

        return $this->respond(
            'collection',
            $weapons,
            new WeaponLeaderboardTransformer
        );
    }

    /**
     * Gets the appropiate field for the table and handles some table naming oddities
     * @param  string $input Field to look at
     * @return string
     */
    public function getField($input) {
        $field = null;
        switch ($input) {
            case 'kills':
                $field = 'killCount';
                break;
            case 'headshots':
                $field = 'headshots';
                break;
            case 'teamkills':
                $field = 'teamkills';
                break;
        }

        return $field;
    }
}
