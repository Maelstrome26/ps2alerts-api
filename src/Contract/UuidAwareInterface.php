<?php

namespace Ps2alerts\Api\Contract;

use Ramsey\Uuid\Uuid;

interface UuidAwareInterface
{
    /**
     * Set the Uuid provider
     *
     * @param \Ramsey\Uuid\Uuid
     */
    public function setUuidDriver(Uuid $uuid);

    /**
     * Get the Uuid provider
     *
     * @return \Ramsey\Uuid\Uuid
     */
    public function getUuidDriver();
}
