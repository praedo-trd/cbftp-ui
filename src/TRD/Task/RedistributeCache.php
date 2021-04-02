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

class RedistributeCache extends Command
{
    protected function configure()
    {
        $this->setName('trd:redistribute_cache')
            ->setDescription('Redistributes the data caches so that they are spread out evenly')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = require_once(__DIR__ . '/../../../app/bootstrap_console.php');

        $output->writeln("\n");

        $items = $app['db']->fetchAll("
            SELECT * FROM data_cache WHERE
            namespace = 'tvmaze'
        ");

        // create a new progress bar (50 units)
        $progress = new ProgressBar($output, sizeof($items));
        $progress->setBarCharacter('<fg=magenta>=</>');
        $progress->setProgressCharacter("\xF0\x9F\x8D\xBA");
        $progress->setFormat("Processing: %message%\n%current%/%max% [%bar%] %percent%%\n");

        // start and displays the progress bar
        $progress->start();

        $dataProvider = new TVMazeDataProvider($app);

        $now = new \DateTime('now', new \DateTimeZone($_ENV['APP_TIMEZONE']));
        $fourteenDaysInSeconds = 60 * 60 * 24 * 14;
        $progression = floor($fourteenDaysInSeconds / sizeof($items));

        foreach ($items as $row) {
            $currentData = unserialize($row['data']);
            if ($app['models']['settings']->get('refresh_ended_shows') === false and $currentData['status'] == 'Ended') {
                continue;
            }

            $newUpdated = $now->sub(new \DateInterval('PT' . $progression . 'S'))->format('Y-m-d H:i:s');
            $progress->setMessage($row['k'] . ' - ' . $newUpdated);

            $app['db']->update('data_cache', array(
              'updated' => $newUpdated
            ), array('id' => $row['id']));

            // advance the progress bar 1 unit
            $progress->advance();
        }

        // ensure that the progress bar is at 100%
        $progress->finish();

        $output->writeln("");
        $output->writeln("<info>Finished</info>");
        $output->writeln("");
        
        return 0;
    }
}
