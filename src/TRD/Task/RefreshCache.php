<?php

namespace TRD\Task;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use TRD\DataProvider\TVMazeDataProvider;

require_once(__DIR__ . '/../../../vendor/autoload.php');
require_once(__DIR__ . '/../../../app/env.php');

class RefreshCache extends Command
{
    protected function configure()
    {
        $this->setName('trd:refresh_cache')
            ->setDescription('Refreshes the data caches')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = require_once(__DIR__ . '/../../../app/bootstrap_console.php');

        $output->writeln("\n");

        // clean out any shit while we're here
        $app['db']->executeQuery("
            DELETE FROM data_cache WHERE k LIKE '%HDTV%'
        ");

        $items = $app['db']->fetchAll("
            SELECT * FROM data_cache WHERE
            namespace = 'tvmaze'
            AND (updated < DATE_SUB(NOW(), INTERVAL 14 DAY)
            OR updated IS NULL)
        ");

        // create a new progress bar (50 units)
        $progress = new ProgressBar($output, sizeof($items));
        $progress->setBarCharacter('<fg=magenta>=</>');
        $progress->setProgressCharacter("\xF0\x9F\x8D\xBA");
        $progress->setFormat("Processing: %message%\n%current%/%max% [%bar%] %percent%%\n");

        // start and displays the progress bar
        $progress->start();

        $dataProvider = new TVMazeDataProvider($app);

        foreach ($items as $row) {
            $currentData = unserialize($row['data']);
            if ($app['models']['settings']->get('refresh_ended_shows') === false and $currentData['status'] == 'Ended') {
                continue;
            }

            $showName = str_replace($row['namespace'] . ':', '', $row['k']);
            $progress->setMessage($showName);

            if (empty($row['id'])) {
                $data = $dataProvider->lookup($showName, true);
            } else {
                $data = $dataProvider->lookupById($row['id']);
            }

            if (!empty($data['id'])) {
                if (empty($row['id'])) {
                    $app['db']->update('data_cache', array(
                        'id' => $data['id']
                    ), array('k' => $row['k']));
                }
                $dataProvider->save($showName, $data);
            } else {
                $app['db']->delete('data_cache', array(
                'id' => $row['id']
              ));
            }

            // advance the progress bar 1 unit
            $progress->advance();

            // stagger it by at least a second
            sleep(1);
        }

        // ensure that the progress bar is at 100%
        $progress->finish();

        $output->writeln("");
        $output->writeln("<info>Finished</info>");
        $output->writeln("");
        
        return 0;
    }
}
