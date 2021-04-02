<?php

namespace TRD\Task;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

require_once(__DIR__ . '/../../../vendor/autoload.php');
require_once(__DIR__ . '/../../../app/env.php');

class Cleanup extends Command
{
    protected function configure()
    {
        $this->setName('trd:cleanup')
            ->setDescription('Runs cleanup tasks')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = require_once(__DIR__ . '/../../../app/bootstrap_console.php');

        $output->writeln("Deleting races older than 30 days");
        $app['db']->executeQuery("DELETE FROM race WHERE created < DATE_SUB(NOW(), INTERVAL 30 DAY)");

        $interval = (int)$app['models']['settings']->get('pre_retention');
        if ($interval > 0) {
            $output->writeln(sprintf("Deleting pres older than %d days", $interval));
            $app['db']->executeQuery("
              DELETE FROM pre WHERE created < DATE_SUB(NOW(), INTERVAL " . $interval . " DAY)
            ");
        } else {
            $output->writeln(sprintf("Not wiping any pres - interval setting is set to %d days", $interval));
        }
        
        return 0;
    }
}
