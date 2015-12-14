<?php

namespace Ps2alerts\Api\Contract;

use Aura\Sql\ExtendedPdo as DBDriver;

trait DatabaseAwareTrait
{
    /**
     * @var \Aura\Sql\ExtendedPdo
     */
    protected $db;

    /**
     * Set the Database driver
     *
     * @param \Aura\Sql\ExtendedPdo $db
     */
    public function setDatabaseDriver(DBDriver $db)
    {
        $this->db = $db;
    }

    /**
     * Get the Database driver
     *
     * @return \Aura\Sql\ExtendedPdo
     */
    public function getDatabaseDriver()
    {
        return $this->db;
    }
}
