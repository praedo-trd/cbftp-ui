<?php

namespace TRD\Task;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use TRD\Utility\CBFTP;

require_once(__DIR__ . '/../../../vendor/autoload.php');
require_once(__DIR__ . '/../../../app/env.php');

class CBFTPTask extends Command
{
    protected function configure()
    {
        $this->setName('trd:cbftp')
            ->setDescription('CBFTP stuff')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = require_once(__DIR__ . '/../../../app/bootstrap_console.php');
        $helper = $this->getHelper('question');
        
        $settings = $app['models']['settings'];
        
        $sites = $app['models']['sites'];
        
        foreach ($sites->getSitesArray() as $target) {
            $bncs = $sites->findAllBNCs($target);

            $cb = new CBFTP($settings->get('cbftp_host'), $settings->get('cbftp_api_port'), $settings->get('cbftp_password'));
                
            $response = $cb->rawCapture("site stat", $bncs);
          
            $updated = false;
            if ($response) {
                if (sizeof($response['successes']) > 0) {
                    foreach ($response['successes'] as $row) {
                        $site = $row['name'];
                        $res = $row['result'];
                        $credits = CBFTP::parseStatToCredits($row['result']);
                
                        $app['models']['sites']->setCredits($target, $site, $credits);
                        $updated = true;
                    }
                }
            }
            if ($updated) {
                $output->writeln("Updated <info>$target</info>");
            }
        }
        return 0;
    }
}
