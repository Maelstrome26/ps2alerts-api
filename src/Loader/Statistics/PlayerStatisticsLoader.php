<?php

namespace Ps2alerts\Api\Loader\Statistics;

use Ps2alerts\Api\Loader\Statistics\AbstractStatisticsLoader;
use Ps2alerts\Api\QueryObjects\QueryObject;
use Ps2alerts\Api\Repository\Statistics\PlayerTotalsRepository;

class PlayerStatisticsLoader extends AbstractStatisticsLoader
{
    /**
     * @var \Ps2alerts\Api\Repository\Statistics\PlayerTotalsRepository
     */
    protected $repository;

    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Repository\Statistics\PlayerTotalsRepository $repository
     */
    public function __construct(PlayerTotalsRepository $repository)
    {
        $this->repository = $repository;
        $this->setCacheNamespace('Statistics');
        $this->setType('Players');
    }

    public function readLeaderboard(array $post)
    {
        $redisKey = "{$this->getCacheNamespace()}:{$this->getType()}:Leaderboards";

        $queryObject = new QueryObject;

        // Build based on metric alone (order by playerKills for example)
        if (! empty($post['metric'] && empty($post['value']))
            && $this->validatePostVars('metric', $post['metric']) === true
        ) {
            $direction = (! empty($post['direction']) ? $post['direction'] : 'desc');

            $queryObject->setOrderBy($post['metric']);
            $queryObject->setOrderByDirection($direction);

            $redisKey .= ":{$post['metric']}-{$direction}";
        }

        // Build based on metric and a value (such as order by playerKills by server)
        if (! empty($post['value'])
            && ! empty($post['metric'])
            && $this->validatePostVars('metric', $post['metric']) === true
        ) {
            $op = (! empty($post['operator']) ? $post['operator'] : '=');

            $queryObject->addWhere([
                'col' => $post['metric'],
                'op'  => $op,
                'value' => $post['value']
            ]);

            $direction = (! empty($post['direction']) ? $post['direction'] : 'desc');

            $queryObject->setOrderBy($post['metric']);
            $queryObject->setOrderByDirection($direction);

            $redisKey .= ":{$post['metric']}{$op}{$post['value']}-{$direction}";
        }

        if (! empty($post['limit'])) {
            $post['limit'] = intval($post['limit']);

            // Hard encode a 50 limit
            if ($post['limit'] > 50) {
                $post['limit'] = 50;
            }

            $queryObject->setLimit($post['limit']);
        } else {
            $post['limit'] = 50;
        }

        $redisKey .= ":limit-{$post['limit']}";

        /*if ($this->checkRedis($redisKey)) {
            return $this->getFromRedis($redisKey);
        }*/

        $this->setCacheExpireTime(3600); // 1 hour

        return $this->cacheAndReturn(
            $this->repository->read($queryObject),
            $redisKey
        );
    }

    public function validatePostVars($field, $value)
    {
        if ($field === 'metric' && ! empty($value)) {
            switch ($value) {
                case 'playerKills':
                case 'playerDeaths':
                case 'playerTeamKills':
                case 'playerSuicides':
                case 'playerFaction':
                case 'headshots':
                case 'playerServer':
                    return true;
                    break;
            }
        }
    }
}
