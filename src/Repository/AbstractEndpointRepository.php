<?php

namespace Ps2alerts\Api\Repository;

use Aura\SqlQuery\AbstractQuery;
use Aura\SqlQuery\QueryFactory;
use Ps2alerts\Api\Contract\DatabaseAwareInterface;
use Ps2alerts\Api\Contract\DatabaseAwareTrait;
use Ps2alerts\Api\Contract\RedisAwareInterface;
use Ps2alerts\Api\Contract\RedisAwareTrait;
use Ps2alerts\Api\Contract\UuidAwareInterface;
use Ps2alerts\Api\Contract\UuidAwareTrait;
use Ps2alerts\Api\QueryObjects\QueryObject;

abstract class AbstractEndpointRepository implements
    DatabaseAwareInterface,
    RedisAwareInterface,
    UuidAwareInterface
{
    use DatabaseAwareTrait;
    use RedisAwareTrait;
    use UuidAwareTrait;

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

    public function fireStatementAndReturn($query, $single = false)
    {
        $pdo = $this->getDatabaseDriver();

        if ($single === false) {
            return $pdo->fetchAll($query->getStatement(), $query->getBindValues());
        }

        return $pdo->fetchOne($query->getStatement(), $query->getBindValues());
    }

    /**
     * Reads a single record from the database
     *
     * @param  string $id
     *
     * @return array
     */
    public function readSingle($id)
    {
        $query = $this->newQuery();

        $query->cols(['*'])
              ->where("{$this->getPrimaryKey()} = {$id}");

        return $this->fireStatementAndReturn($query, true);
    }
}
