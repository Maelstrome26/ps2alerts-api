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

        $playersProcessed = $this->processPlayers($alert);
        $output->writeln("{$playersProcessed} players processed");

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
            'playerSuicides AS suicides',
            'headshots'
        ]);
        $query->from('ws_players');

        $query->where('resultID = ?', $alert->ResultID);
        //$query->where('playerID = ?', '5428010618035323201');

        $playerQuery = $this->db->prepare($query->getStatement());
        $playerQuery->execute($query->getBindValues());

        $count = 0;

        while($row = $playerQuery->fetch(\PDO::FETCH_OBJ)) {

            $count++;

            $update = $this->auraFactory->newUpdate();
            $update->table('ws_players_total');
            $update->where('playerID = ?', $row->playerID);

            $update->set('playerKills', "playerKills - {$row->kills}");
            $update->set('playerDeaths', "playerDeaths - {$row->deaths}");
            $update->set('playerTeamKills', "playerTeamKills - {$row->teamkills}");
            $update->set('playerSuicides', "playerSuicides - {$row->suicides}");
            $update->set('headshots', "headshots - {$row->headshots}");

            $updateQuery = $this->db->prepare($update->getStatement());
            $updateQuery->execute($update->getBindValues());
        }

        return $count;
    }
}
