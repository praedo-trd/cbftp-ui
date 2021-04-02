<?php

namespace TRD\Task;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

require_once(__DIR__ . '/../../../vendor/autoload.php');
require_once(__DIR__ . '/../../../app/env.php');

class ImportTVRageCache extends Command
{
    protected function configure()
    {
        $this->setName('trd:tvrageimport')
            ->setDescription('Imports from the nTrader tvrage cache')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = require_once(__DIR__ . '/../../../app/bootstrap_console.php');

        $output->writeln("<info>Trying to import tvrage data</info>");

        $data = file(__DIR__ . '/../../../misc/tvrage.cache');
        $lastShow = null;
        $lastString = null;
        $shows = array();
        foreach ($data as $c => $line) {
            $line = trim($line);

            // site
            if ($c % 2 == 0) {
                $lastShow = str_replace('.', ' ', $line);
            } // site string
            else {
                if (!empty($line)) {
                    $shows["$lastShow"] = $line;
                    ;
                }
            }
            $c++;
        }

        $tvmaze = new \TRD\DataProvider\TVMazeDataProvider($app);

        $new = 0;
        foreach ($shows as $showName => $showInfo) {
            if (!$tvmaze->exists($showName)) {
                $new++;
            }
        }

        $output->writeln(sprintf("Found <info>%d</info> shows and <info>%d</info> new ones to import", sizeof($shows), $new));
        
        return 0;
    }
}
