<?php

namespace Ps2alerts\Api\Contract;

use Ramsey\Uuid\Uuid;

trait UuidAwareTrait
{
    /**
     * @var \Ramsey\Uuid\Uuid
     */
    protected $uuid;

    /**
     * Set the Uuid driver
     *
     * @param \Ramsey\Uuid\Uuid $uuid
     */
    public function setUuidDriver(Uuid $uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * Get the Uuid driver
     *
     * @return \Ramsey\Uuid\Uuid
     */
    public function getUuidDriver()
    {
        return $this->uuid;
    }
}
