<?php

namespace Ps2alerts\Api\Command;

use Ps2alerts\Api\Command\BaseCommand;
use Ps2alerts\Api\Repository\AlertRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ArchiveCommand extends BaseCommand
{
    protected $recordsArchived = 0;

    protected function configure()
    {
        parent::configure(); // See BaseCommand.php
        $this
            ->setName('Archive')
            ->setDescription('Archives old alerts');
        global $container; // Inject container
        $this->dbArchive = $container->get('Database\Archive');
        $this->alertRepo = $container->get('Ps2alerts\Api\Repository\AlertRepository');
    }

    /**
     * Execution
     *
     * @param  Symfony\Component\Console\Input\InputInterface   $input
     * @param  Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Executing archive operations...');

        $this->check($output);
    }

    /**
     * Checks for alerts to be archived then runs routing against said alerts
     *
     * @param  Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    public function check(OutputInterface $output)
    {
        $obj = new \DateTime();
        $obj->sub(new \DateInterval('P14D')); // Two weeks ago

        $query = $this->alertRepo->newQuery();
        $query->cols(['*']);
        $query->where('ResultStartTime < ?', $obj->format('U'));
        $query->where('Archived = 0');

        $alerts = $this->alertRepo->fireStatementAndReturn($query);
        $count = count($alerts);

        $output->writeln("Detected {$count} alerts to be archived");

        if ($count > 0) {
            $tables = [
                'ws_classes',
                'ws_classes_totals',
                'ws_combat_history',
                'ws_factions',
                'ws_map',
                'ws_map_initial',
                'ws_outfits',
                'ws_players',
                'ws_pops',
                'ws_vehicles',
                'ws_weapons',
                'ws_xp'
            ];

            for ($i=0; $i < $count; $i++) {
                $this->archive($alerts[$i], $tables, $output);
            }

            $output->writeln("Archived {$this->recordsArchived} records!");
        }
    }

    /**
     * Execution of routine
     *
     * @param  array                                            $alert
     * @param  array                                            $tables
     * @param  Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    public function archive($alert, $tables, OutputInterface $output)
    {
        $output->writeln("Processing Alert #{$alert['ResultID']}");

        // Get all data and insert it into the archive DB
        foreach($tables as $table) {
            $output->writeln("Alert #{$alert['ResultID']} - Table: {$table}");
            $sql = "SELECT * FROM {$table} WHERE resultID = :result";
            $binds = [
                'result' => $alert['ResultID']
            ];

            foreach ($this->db->yieldAll($sql, $binds) as $row) {
                $cols   = $this->buildCols($row);
                $values = $this->buildValues($row);

                $sql = "INSERT INTO {$table} ({$cols}) VALUES ('{$values}')";

                $stm = $this->dbArchive->prepare($sql);
                $stm->execute();
            }
        }

        // Loop through all tables and delete the alert's data from the DB
        foreach($tables as $table) {
            $sql = "DELETE FROM {$table} WHERE resultID = :result";
            $stm = $this->db->prepare($sql);
            $stm->execute(['result' => $alert['ResultID']]);
            $output->writeln("Archived {$stm->rowCount()} from Alert #{$alert['ResultID']} - Table {$table}");

            $this->recordsArchived += $stm->rowCount();
        }

        // Set the alert as archived in the resultset
        $sql = "UPDATE ws_results SET Archived = '1' WHERE ResultID = :result";
        $stm = $this->db->prepare($sql);
        $stm->execute(['result' => $alert['ResultID']]);
    }

    /**
     * Builds the columns of the insert query
     *
     * @param  array $row
     *
     * @return string
     */
    public function buildCols($row)
    {
        $keys = [];
        foreach($row as $key => $val) {
            $keys[] = (string) $key;
        }

        return implode(",", $keys);
    }

    /**
     * Builds the values of the insert query
     *
     * @param  array $row
     *
     * @return string
     */
    public function buildValues($row)
    {
        $values = [];
        foreach($row as $key => $val) {
            $values[] = $val;
        }

        return implode("','", $values);
    }
}
