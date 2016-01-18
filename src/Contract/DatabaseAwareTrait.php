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
     * @var \Aura\Sql\ExtendedPdo
     */
    protected $dbData;

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

    /**
     * Set the Database Data driver
     *
     * @param \Aura\Sql\ExtendedPdo $dbData
     */
    public function setDatabaseDataDriver(DBDriver $dbData)
    {
        $this->dbData = $dbData;
    }

    /**
     * Get the Database Data driver
     *
     * @return \Aura\Sql\ExtendedPdo
     */
    public function getDatabaseDataDriver()
    {
        return $this->dbData;
    }
}
