<?php

namespace Ps2alerts\Api\Command;

use Ps2alerts\Api\Repository\AlertRepository;
use Ps2alerts\Api\Command\DeleteAlertCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteMissingAlertsCommand extends Command
{
    protected $alertRepo;
    protected $auraFactory;
    protected $db;
    protected $alertProcessor;

    protected function configure()
    {
        $this
            ->setName('DeleteMissingAlerts')
            ->setDescription('Deletes all missing alerts')
        ;

        global $container; // Inject Container
        $this->alertRepo = $container->get('Ps2alerts\Api\Repository\AlertRepository');
        $this->auraFactory = $container->get('Ps2alerts\Api\Factory\AuraFactory');
        $this->alertProcessor = new DeleteAlertCommand();
        $this->db = $container->get('Database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $query = $this->auraFactory->newSelect();
        $query->from('ws_results');
        $query->cols(['ResultID']);
        $query->orderBy(['ResultID DESC']);

        $allQuery = $this->db->prepare($query->getStatement());
        $allQuery->execute($query->getBindValues());

        $result = $allQuery->fetch(\PDO::FETCH_OBJ);

        $count = 0;
        $missing = 0;
        $max = $result->ResultID;

        while($count < $max) {
            $count++;

            var_dump($count);

            $alertPDO = $this->auraFactory->newSelect();
            $alertPDO->from('ws_results');
            $alertPDO->cols(['ResultID']);
            $alertPDO->where('ResultID = ?', $count);

            $alertQuery = $this->db->prepare($alertPDO->getStatement());
            $alertQuery->execute($alertPDO->getBindValues());

            $alert = $alertQuery->fetch(\PDO::FETCH_OBJ);

            if (empty($alert)) {
                $missing++;
                $output->writeln("ALERT #{$count} DOES NOT EXIST!");
                $this->alertProcessor->processAlert($count, $output, true);
            }
        }

        $output->writeln("{$missing} missing alerts processed!");
    }
}
