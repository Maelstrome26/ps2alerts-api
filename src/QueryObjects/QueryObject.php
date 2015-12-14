<?php

namespace Ps2alerts\Api\QueryObjects;

class QueryObject
{
    /**
     * @var array
     */
    public $wheres;

    /**
     * @var string
     */
    public $orderBy;

    /**
     * @var string
     */
    public $orderByDirection = 'asc';

    /**
     * @var string
     */
    public $dimension = 'multi';

    /**
     * Adds where statements to the object
     *
     * @param array $array
     */
    public function addWhere($array)
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
     * Set order by column
     *
     * @param [type] $string [description]
     */
    public function setOrderBy($string)
    {
        $this->orderBy = $string;
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
        $this->orderByDirection = $string;
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
     * Set asc or desc
     *
     * @param string $string
     */
    public function setDimension($string)
    {
        $this->dimension = $string;
    }

    /**
     * Gets order by direction
     *
     * @return string
     */
    public function getDimension()
    {
        return $this->dimension;
    }
}
