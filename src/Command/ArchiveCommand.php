<?php

namespace Ps2alerts\Api\Command;

use Ps2alerts\Api\Command\BaseCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ArchiveCommand extends BaseCommand
{
    protected function configure()
    {
        parent::configure(); // See BaseCommand.php
        $this
            ->setName('archiveCommand')
            ->setDescription('Archives old alerts');
        global $container; // Inject container
        $this->alertRepo = $container->get('AlertRepository');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
    }
}
