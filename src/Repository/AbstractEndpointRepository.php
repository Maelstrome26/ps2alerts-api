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

    /**
     * Executes the statement to the DB and returns the results
     *
     * @param  \Aura\SqlQuery\AbstractQuery $query
     * @param  boolean                      $single
     *
     * @return array
     */
    public function fireStatementAndReturn($query, $single = false)
    {
        $pdo = $this->getDatabaseDriver();

        if ($single === false) {
            return $pdo->fetchAll($query->getStatement(), $query->getBindValues());
        }

        return $pdo->fetchOne($query->getStatement(), $query->getBindValues());
    }

    /**
     * Allows for other controllers to build a query there and then request it here
     *
     * @param  \Aura\SqlQuery\AbstractQuery $query
     * @param  boolean                      $single
     *
     * @return array
     */
    public function readRaw($query, $single = false)
    {
        return $this->fireStatementAndReturn($query, $single);
    }

    /**
     * Reads a single record from the database
     *
     * @param  string $id
     *
     * @return array
     */
    public function readSinglebyId($id, $keyType = 'primary')
    {
        $query = $this->newQuery();
        $key = $this->returnKeyType($keyType);

        $query->cols(['*'])
              ->where("`{$key}` = '{$id}'");

        return $this->fireStatementAndReturn($query, true);
    }

    /**
     * Reads all related records from the database
     *
     * @param  string $id
     *
     * @return array
     */
    public function readAllById($id, $keyType = 'primary')
    {
        $query = $this->newQuery();
        $key = $this->returnKeyType($keyType);

        $query->cols(['*'])
              ->where("`{$key}` = '{$id}'");

        return $this->fireStatementAndReturn($query);
    }

    /**
     * Reads all records based off a simple where statement
     *
     * @param  array $fields
     *
     * @return array
     */
    public function readAllByFields($fields)
    {
        $query = $this->newQuery();

        $query->cols(['*']);

        foreach ($fields as $field => $value) {
            $query->where("`{$field}` = '{$value}'");
        }

        return $this->fireStatementAndReturn($query);
    }

    /**
     * Reads the count of records based off a where statement
     *
     * @param  array $fields
     *
     * @return array
     */
    public function readCountByFields($fields)
    {
        $query = $this->newQuery();
        $key   = $this->returnKeyType('primary');

        $query->cols(["COUNT({$key}) as COUNT"]);

        foreach ($fields as $field => $value) {
            $query->where("`{$field}` = '{$value}'");
        }

        $result = $this->fireStatementAndReturn($query);

        // Done this to prevent the need for clients to also do this. Returns a single number this way.
        return $result[0]["COUNT"];
    }

    /**
     * Sets the proper key to search on based off a string
     *
     * @param  string $key
     *
     * @return string
     */
    public function returnKeyType($key)
    {
        switch ($key) {
            case 'result':
                return $this->getResultKey();
            case 'primary':
            default:
                return $this->getPrimaryKey();
        }
    }
}
