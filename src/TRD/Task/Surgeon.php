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

class Surgeon extends Command
{
    protected function configure()
    {
        $this->setName('trd:surgeon')
            ->setDescription('Fixes config files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = require_once(__DIR__ . '/../../../app/bootstrap_console.php');

        $output->writeln("");

        $siteModel = $app['models']['sites'];


        $output->writeln("");
        $output->writeln("<comment>Fixing any sites with missing top-level keys keys:</comment>");
        foreach ($siteModel->getData() as $siteName => $siteInfo) {
            foreach ($siteInfo->sections as $section) {
                $repaired = $siteModel->repairSite($siteName);
                if ($repaired) {
                    $output->writeln(sprintf("<error>Repaired</error> %s on %s", $section->name, $siteName));
                }
            }
        }

        $output->writeln("");
        $output->writeln("<comment>Fixing any sites with missing section keys:</comment>");
        foreach ($siteModel->getData() as $siteName => $siteInfo) {
            foreach ($siteInfo->sections as $section) {
                $repaired = $siteModel->repairSection($siteName, $section->name);
                if ($repaired) {
                    $output->writeln(sprintf("<error>Repaired</error> %s on %s", $section->name, $siteName));
                }
            }
        }

        $output->writeln("");
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            '<comment>Do you want to apply the default skiplists to all sections?</comment> [y|n]: ',
            false,
            '/^(y|j)/i'
        );

        if ($helper->ask($input, $output, $question)) {
            $defaultSkiplists = $app['models']['settings']->get('default_skiplists');
            if (sizeof($defaultSkiplists) == 0) {
                $output->writeln("<error>You have no default skiplists set</error>");
            } else {
                $total = 0;
                foreach ($defaultSkiplists as $skiplistName) {
                    $total += $siteModel->applySkiplistToEverySection($skiplistName);
                }
                $output->writeln(sprintf("Applied %d skiplists to %d sections", sizeof($defaultSkiplists), $total));
            }
        }

        $autoRules = json_decode(file_get_contents($_ENV['DATA_PATH'] . '/autorules.json'), true);
        if (!isset($autoRules['rules'])) {
            $output->writeln("<error>Incorrect autorules format - attempting to fix....</error>");
            $newObject = array(
            'rules' => $autoRules,
            'schedule' => array(
              1 => array('00:00', '23:59'),
              2 => array('00:00', '23:59'),
              3 => array('00:00', '23:59'),
              4 => array('00:00', '23:59'),
              5 => array('00:00', '23:59'),
              6 => array('00:00', '23:59'),
              7 => array('00:00', '23:59')
            )
          );
            file_put_contents($_ENV['DATA_PATH'] . '/autorules.json', json_encode($newObject, JSON_PRETTY_PRINT));
            $output->writeln("Fixed autorules!");
        }

        $settings = json_decode(file_get_contents($_ENV['DATA_PATH'] . '/settings.json'), true);
        if (!isset($settings['schedule'])) {
            $output->writeln("<error>Adding missing schedule key to settings</error>");
            $settings['schedule'] = array(
            1 => array('00:00', '23:59'),
            2 => array('00:00', '23:59'),
            3 => array('00:00', '23:59'),
            4 => array('00:00', '23:59'),
            5 => array('00:00', '23:59'),
            6 => array('00:00', '23:59'),
            7 => array('00:00', '23:59')
          );

            file_put_contents($_ENV['DATA_PATH'] . '/settings.json', json_encode($settings, JSON_PRETTY_PRINT));
            $output->writeln("Fixed settings!");
        }
        return 0;
    }
}
