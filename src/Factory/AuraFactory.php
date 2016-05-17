<?php

namespace Ps2alerts\Api\Factory;

use Aura\SqlQuery\QueryFactory;

class AuraFactory
{
    protected $factory;

    public function __construct(QueryFactory $aura)
    {
        $this->factory = $aura;
    }

    public function newSelect()
    {
        return $this->factory->newSelect();
    }
}
