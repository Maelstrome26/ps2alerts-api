<?php

namespace Ps2alerts\Api\Repository;

use Aura\SqlQuery\AbstractQuery;
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

    /**
     * Determines the Result key of the table
     *
     * @return string
     */
    abstract public function getResultKey();

    /**
     * Builds a new query factory ready for use with the QueryObjects
     *
     * @return \Aura\SqlQuery\AbstractQuery
     */
    public function newQuery()
    {
        $factory = new QueryFactory('mysql');

        $query = $factory->newSelect(); // Suspect I'll only ever need this one
        $query->from($this->getTable());

        return $query;
    }

    /**
     * Sets up the resulting query based off properties present in the supplied object.
     *
     * @see \Ps2alerts\Api\QueryObjects\QueryObject
     *
     * @param  \Ps2alerts\Api\QueryObjects\QueryObject $queryObject
     * @return array
     */
    public function read(QueryObject $queryObject)
    {
        $query = $this->newQuery();
        $query->cols(['*']);

        // Workarounds :-/
        if (! empty($queryObject->getFlags())) {
            if ($queryObject->getFlags() === 'outfitIDs') {
                // Prevent the VS, NC and TR "no outfit" workaround
                $queryObject->addWhere([
                    'col' => 'outfitID',
                    'op' => '>',
                    'value' => 0
                ]);
            }
        }

        // Setup where statements
        if (! empty($queryObject->getWheres())) {
            foreach ($queryObject->getWheres() as $where) {
                if ($where['col'] === 'primary') {
                    $col = $this->getPrimaryKey();
                } elseif ($where['col'] === 'result') {
                    $col = $this->getResultKey();
                } else {
                    $col = $where['col'];
                }

                $op = (isset($where['op']) ? $where['op'] : '=');
                $query->where("`{$col}` {$op} {$where['value']}");
            }
        }

        // Set up order statement
        if (! empty($queryObject->getOrderBy())) {
            $orderBy = $queryObject->getOrderBy();
            if ($orderBy === 'primary') {
                $orderBy = $this->getPrimaryKey();
            } elseif ($orderBy === 'result') {
                $orderBy = $this->getResultKey();
            }
            
            $query->orderBy([
                "`{$orderBy}` {$queryObject->getOrderByDirection()}"
            ]);
        }

        if (! empty($queryObject->getLimit())) {
            $query->limit($queryObject->getLimit());
        }

        return $this->prepareAndExecuteQuery($query, $queryObject);
    }

    /**
     * Sets up the PDO Driver, then executes the query based on dimension.
     *
     * @param  \Aura\SqlQuery\AbstractQuery $query
     * @param  \Ps2alerts\Api\QueryObjects\QueryObject $queryObject Sent QueryObject to read dimension
     * @return array The final data
     */
    public function prepareAndExecuteQuery(AbstractQuery $query, QueryObject $queryObject)
    {
        $pdo = $this->getDatabaseDriver();

        if ($queryObject->getDimension() === 'multi') {
            return $pdo->fetchAll($query->getStatement());
        }

        return $pdo->fetchOne($query->getStatement());
    }
}
