<?php

namespace TRD\Task;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use TRD\DataProvider\DataProvider;
use TRD\DataProvider\IMDBDataProvider;
use TRD\Utility\ReleaseName;

require_once(__DIR__ . '/../../../vendor/autoload.php');
require_once(__DIR__ . '/../../../app/env.php');

class AutoTV extends Command
{
    protected function configure()
    {
        $this->setName('trd:auto_tv')
            ->setDescription('Imports preliminary information for auto tv section')
            ->addOption(
                'all',
                null,
                InputOption::VALUE_NONE,
                'If set, the task will import everything, not just US scripted series'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = require_once(__DIR__ . '/../../../app/bootstrap_console.php');

        $output->writeln("<info>Checking for new shows</info>");

        $style = new \Symfony\Component\Console\Formatter\OutputFormatterStyle('red', 'default', array());
        $output->getFormatter()->setStyle('red', $style);

        $rows = $app['db']->fetchAll("
            SELECT * FROM data_cache WHERE namespace = 'tvmaze'
        ");

        foreach ($rows as $row) {
            $info = unserialize($row['data']);
            $rlsname = str_replace('tvmaze:', '', $row['k']);
            $show = str_replace(' ', '.', $rlsname);

            if (!empty($info['latest_season'])) {
                if ($input->getOption('all') === false and ($info['country'] !== 'United States' or $info['classification'] !== 'Scripted')) {
                    continue;
                }

                try {
                    $now = new \DateTime('now', new \DateTimeZone($_ENV['APP_TIMEZONE']));
                    $app['db']->insert('auto_tv', array(
                        'title' => $show,
                        'season' => $info['latest_season'],
                        'created' => $now->format('Y-m-d H:i')
                    ));
                } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                    // ignore...
                }
            }
        }
        
        return 0;
    }
}
