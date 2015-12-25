<?php

namespace Ps2alerts\Api\Loader\Statistics;

use Ps2alerts\Api\Loader\Statistics\AbstractStatisticsLoader;
use Ps2alerts\Api\QueryObjects\QueryObject;
use Ps2alerts\Api\Repository\AlertRepository;

class AlertStatisticsLoader extends AbstractStatisticsLoader
{
    /**
     * @var \Ps2alerts\Api\Repository\AlertRepository
     */
    protected $repository;

    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Repository\AlertRepository $repository
     */
    public function __construct(AlertRepository $repository)
    {
        $this->repository = $repository;
        $this->setCacheNamespace('Statistics');
        $this->setType('Alerts');
    }

    /**
     * Read total counts for alerts
     *
     * @param  array $post
     *
     * @return array
     */
    public function readTotals($post)
    {
        $redisKey = "{$this->getCacheNamespace()}:{$this->getType()}:Totals";

        $queryObject = new QueryObject;
        $queryObject->addSelect('COUNT(ResultID) AS COUNT');
        $queryObject->addWhere([
            'col'   => 'Valid',
            'value' => '1'
        ]);

        if (! empty($post['ResultServer'])) {
            if ($this->validatePostVars('ResultServer', $post['ResultServer']) === true) {
                $queryObject->addWhere([
                    'col'   => 'ResultServer',
                    'value' => $post['ResultServer']
                ]);
                $redisKey .= ":Server-{$post['ResultServer']}";
            }
        }

        if (! empty($post['ResultWinner'])) {
            if ($this->validatePostVars('ResultWinner', $post['ResultWinner']) === true) {
                $queryObject->addWhere([
                    'col'   => 'ResultWinner',
                    'value' => $post['ResultWinner']
                ]);
                $redisKey .= ":Winner-{$post['ResultWinner']}";
            }
        }

        if (! empty($post['ResultAlertCont'])) {
            if ($this->validatePostVars('ResultAlertCont', $post['ResultAlertCont']) === true) {
                $queryObject->addWhere([
                    'col'   => 'ResultAlertCont',
                    'value' => $post['ResultAlertCont']
                ]);
                $redisKey .= ":Cont-{$post['ResultAlertCont']}";
            }
        }

        if (! empty($post['ResultDomination'])) {
            if ($this->validatePostVars('ResultDomination', $post['ResultDomination']) === true) {
                $queryObject->addWhere([
                    'col'   => 'ResultDomination',
                    'value' => $post['ResultDomination']
                ]);
                $redisKey .= ":Domination-{$post['ResultDomination']}";
            }
        }

        if ($this->checkRedis($redisKey)) {
            return $this->getFromRedis($redisKey);
        }

        $this->setCacheExpireTime(900); // 15 mins

        return $this->cacheAndReturn(
            $this->repository->read($queryObject),
            $redisKey
        );
    }

    /**
     * Validates POST input
     *
     * @param  string $type   Type of variable to check
     * @param  string $value
     *
     * @return boolean
     */
    public function validatePostVars($type, $value)
    {
        if ($type === 'ResultServer') {
            $value = intval($value); // Convert to integer if needed
            switch ($value) {
                case 1:
                case 10:
                case 13:
                case 17:
                case 19:
                case 25:
                    return true;
            }
        }

        if ($type === 'ResultWinner') {
            $value = strtoupper($value);
            switch ($value) {
                case 'VS':
                case 'NC':
                case 'TR':
                case 'DRAW':
                    return true;
                    break;
            }
        }

        if ($type === 'ResultAlertCont') {
            $value = intval($value);
            switch ($value) {
                case 2:
                case 4:
                case 6:
                case 8:
                    return true;
                    break;
            }
        }

        if ($type === 'ResultDomination') {
            $value = intval($value);
            switch ($value) {
                case 0:
                case 1:
                    return true;
                    break;
            }
        }

        return false;
    }
}
