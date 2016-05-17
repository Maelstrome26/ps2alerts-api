<?php

namespace Ps2alerts\Api\Command;

use Ps2alerts\Api\Repository\AlertRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteAlertCommand extends Command
{
    protected $alertRepo;
    protected $auraFactory;
    protected $db;

    protected function configure()
    {
        $this
            ->setName('DeleteAlert')
            ->setDescription('Deletes an alert and corrects totals')
            ->addArgument(
                'alert',
                InputArgument::REQUIRED,
                'Alert ID to process'
            )
        ;

        global $container; // Inject Container
        $this->alertRepo = $container->get('Ps2alerts\Api\Repository\AlertRepository');
        $this->auraFactory = $container->get('Ps2alerts\Api\Factory\AuraFactory');
        $this->db = $container->get('Database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('alert');

        $alert = $this->alertRepo->readSingleById($id, 'primary', true);

        $this->processPlayers($alert);

        /*
        Needs to do:
        * Delete records:
            * Players
            * Outfits
            * Vehicles
            * Weapons
            * XP
            * Map
            * Map Initial
            * Class Combat
            * Combat History
            * Population History
            * Factions record
            * The result itself
        * Recalculate global totals:
            * Player Totals
            * Outfit Totals
            * Vehicle Totals
            * Weapon Totals
            * XP Totals
         */

        $output->writeln($text);
    }

    protected function processPlayers($alert)
    {
        $query = $this->auraFactory->newSelect();
        $query->cols([
            'playerName',
            'playerID',
            'playerKills AS kills',
            'playerDeaths AS deaths',
            'playerTeamKills AS teamkills',
            'playerSuicides AS suicides'
        ]);
        $query->from('ws_players');

        $query->where('resultID = ?', $alert->ResultID);

        $statement = $this->db->prepare($query->getStatement());
        $statement->execute($query->getBindValues());

        while($row = $statement->fetch(\PDO::FETCH_OBJ)) {
            var_dump($row);
        }
    }
}
