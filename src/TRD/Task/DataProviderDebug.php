<?php

namespace TRD\Task;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use TRD\DataProvider\IMDBDataProvider;
use TRD\DataProvider\TVMazeDataProvider;

require_once(__DIR__ . '/../../../vendor/autoload.php');
require_once(__DIR__ . '/../../../app/env.php');

class DataProviderDebug extends Command
{
    protected function configure()
    {
        $this->setName('trd:data_provider_debug')
            ->setDescription('Debug a data provider lookup')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = require_once(__DIR__ . '/../../../app/bootstrap_console.php');
        $helper = $this->getHelper('question');

        $validProviders = ['imdb', 'tvmaze'];
        $provider = $helper->ask($input, $output, new Question(
            sprintf('Choose a data provider [%s]: ', implode('|', $validProviders))
        ));
        if (empty($provider)) {
            return $output->writeln("<error>Please enter a valid data provider</error>");
        }
        
        switch ($provider) {
          case 'imdb':
            $provider = new IMDBDataProvider($app);
            break;
          
          case 'tvmaze':
            $provider = new TVMazeDataProvider($app);
            break;
            
          default:
            $output->writeln("<error>Please enter a valid data provider</error>");
            return 0;
        }
        
        $lookup = $helper->ask($input, $output, new Question('Enter a rlsname.cleaned: '));
        if (empty($lookup)) {
            return $output->writeln("<error>Please enter a rlsname.cleaned</error>");
        }
        
        $response = $provider->lookup($lookup, true);
        $debug = $provider->getDebug();
        
        foreach ($debug as $line) {
            $output->writeln(" > $line");
        }
        
        return 0;
    }
}
