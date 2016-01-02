<?php

namespace Ps2alerts\Api\QueryObjects;

class QueryObject
{
    /**
     * @var array
     */
    protected $selects;

    /**
     * @var array
     */
    protected $wheres;

    /**
     * @var array
     */
    protected $whereIns;

    /**
     * @var string
     */
    protected $orderBy;

    /**
     * @var string
     */
    protected $orderByDirection = 'asc';

    /**
     * @var string
     */
    protected $dimension = 'multi';

    /**
     * Limit of records to bring back
     *
     * @var integer
     */
    protected $limit = null;

    /**
     * Place to set special workaround flags
     *
     * @var string|array
     */
    protected $flags;

    /**
     * Adds select statement parameters to the object
     * Expects a string column name, which is then looped through
     * in the array and added to.
     * e.g. $queryObject->addSelect('COUNT(ResultID) AS COUNT');
     *
     * @param string $string
     */
    public function addSelect($array)
    {
        $this->selects[] = $array;
    }

    /**
     * Gets the select statement parameters out of the object
     *
     * @return array
     */
    public function getSelects()
    {
        return $this->selects;
    }

    /**
     * Adds where statements to the object
     *
     * @param array $array
     */
    public function addWhere(array $array)
    {
        $this->wheres[] = $array;
    }

    /**
     * Pulls out the array for the where statements
     *
     * @return array
     */
    public function getWheres()
    {
        return $this->wheres;
    }

    /**
     * Adds where statements to the object
     *
     * @param array $array
     */
    public function addWhereIn(array $array)
    {
        $this->whereIns[] = $array;
    }

    /**
     * Pulls out the array for the where statements
     *
     * @return array
     */
    public function getWhereIns()
    {
        return $this->whereIns;
    }

    /**
     * Set order by column
     *
     * @param string $string [description]
     */
    public function setOrderBy($string)
    {
        if (! empty($string)) {
            $this->orderBy = $string;
        }
    }

    /**
     * Get order by column
     *
     * @return string
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * Set asc or desc
     *
     * @param string $string
     */
    public function setOrderByDirection($string)
    {
        if (! empty($string)) {
            $this->orderByDirection = $string;
        }
    }

    /**
     * Gets order by direction
     *
     * @return string
     */
    public function getOrderByDirection()
    {
        return $this->orderByDirection;
    }

    /**
     * Set array dimension level
     *
     * @param string $string
     */
    public function setDimension($string)
    {
        if (! empty($string)) {
            $this->dimension = $string;
        }
    }

    /**
     * Gets array dimension level
     *
     * @return string
     */
    public function getDimension()
    {
        return $this->dimension;
    }

    /**
     * Sets the limit of records to bring back
     *
     * @param integer $limit
     */
    public function setLimit($limit)
    {
        if (! empty($limit)) {
            $this->limit = $limit;
        }
    }

    /**
     * Gets the limit of records
     *
     * @return integer
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Allows setting of workaround flags
     *
     * @param array|string $flags
     */
    public function setFlags($flags)
    {
        if (! empty($flags)) {
            $this->flags = $flags;
        }
    }

    /**
     * Retreives workaround flags
     *
     * @return array|string
     */
    public function getFlags()
    {
        return $this->flags;
    }
}
