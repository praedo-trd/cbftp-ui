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

class SimulateRace extends Command
{
    protected function configure()
    {
        $this->setName('trd:simulate')
            ->setDescription('Simulates a race evaluation')
            ->addArgument('tag', InputArgument::REQUIRED, 'The tag for the race')
            ->addArgument('pattern', InputArgument::REQUIRED, 'The pattern for the release name (can contain wildcards)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = require_once(__DIR__ . '/../../../app/bootstrap_console.php');

        $race = new \TRD\Race\Race($app);
        $result = $race->race($input->getArgument('tag'), $input->getArgument('pattern'));
        print_r($result);

        // if (extension_loaded('xhprof')) {
        //     $xhprof_data = xhprof_disable();
        //
        //     $XHPROF_ROOT = $_SERVER['HOME'] . "/Projects/xhprof/";
        //     include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
        //     include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";
        //
        //     $xhprof_runs = new \XHProfRuns_Default();
        //     $run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_testing");
        //
        //     echo "http://localhost/xhprof/xhprof_html/index.php?run={$run_id}&source=xhprof_testing\n";
        // }

        //var_dump($result);
        return 0;
    }
}
