<?php

namespace Ps2alerts\Api\Repository;

use Aura\SqlQuery\QueryFactory;
use Ps2alerts\Api\Contract\DatabaseAwareInterface;
use Ps2alerts\Api\Contract\DatabaseAwareTrait;
use Ps2alerts\Api\Contract\RedisAwareInterface;
use Ps2alerts\Api\Contract\RedisAwareTrait;
use Ps2alerts\Api\QueryObjects\QueryObject;

abstract class AbstractEndpointRepository implements
    DatabaseAwareInterface,
    RedisAwareInterface
{
    use DatabaseAwareTrait;
    use RedisAwareTrait;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $primary;

    /**
     * Determines the table that the DB is interfacing with
     *
     * @return string
     */
    abstract public function getTable();

    /**
     * Determines the primary key of the table
     *
     * @return string
     */
    abstract public function getPrimaryKey();

    public function newQuery($type)
    {
        $factory = new QueryFactory('mysql');

        switch ($type) {
            case 'select':
                $query = $factory->newSelect();
                break;
            case 'where':
                $query = $factory->newWhere();
                break;
            default:
                break;
        }

        $query->from($this->getTable());

        return $query;
    }

    public function prepareAndExecuteQuery($statement, QueryObject $queryObject)
    {
        $pdo = $this->getDatabaseDriver();

        if ($queryObject->getDimension() === 'multi') {
            return $pdo->fetchAll($statement->getStatement());
        }

        return $pdo->fetchOne($statement->getStatement());
    }

    /**
     * Executes read statement
     *
     * @see \Ps2alerts\Api\QueryObjects\QueryObject
     * @param  array $wheres The array of where statements to look for.
     * @return array
     */
    public function read(QueryObject $queryObject)
    {
        $query = $this->newQuery('select');
        $query->cols(['*']);

        // Setup where statements
        if (! empty($queryObject->getWheres())) {
            foreach ($queryObject->getWheres() as $where) {
                $col = ($where['col'] === 'primary') ? $this->getPrimaryKey() : $where['col'];
                $op = (isset($where['op']) ? $where['op'] : '=');
                $query->where("{$col} {$op} {$where['value']}");
            }
        }

        // Set up order statement
        if (! empty($queryObject->getOrderBy())) {
            $orderBy = $queryObject->getOrderBy();

            $orderByString = ($orderBy === 'primary') ? $this->getPrimaryKey() : $orderBy;
            $orderByString .= " {$queryObject->getOrderByDirection()}";

            $query->orderBy([$orderByString]);
        }

        return $this->prepareAndExecuteQuery($query, $queryObject);
    }
}
