<?php

namespace Ps2alerts\Api\Command;

use Symfony\Component\Console\Command\Command;

class BaseCommand extends Command
{
    protected $db;
    protected $auraFactory;

    protected function configure()
    {
        global $container; // Inject Container

        $this->auraFactory = $container->get('Ps2alerts\Api\Factory\AuraFactory');
        $this->db          = $container->get('Database');
    }
}
