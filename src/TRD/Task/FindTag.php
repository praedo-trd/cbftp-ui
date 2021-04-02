<?php

namespace TRD\Task;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Helper\Table;
use TRD\Utility\ReleaseName;

require_once(__DIR__ . '/../../../vendor/autoload.php');
require_once(__DIR__ . '/../../../app/env.php');

class FindTag extends Command
{
    protected function configure()
    {
        $this->setName('trd:find_tag')
            ->setDescription('Find a tag based off site + section + rlsname')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = require_once(__DIR__ . '/../../../app/bootstrap_console.php');
        $siteModel = $app['models']['sites'];
        $helper = $this->getHelper('question');

        $site = $helper->ask($input, $output, new Question('Please enter the site name: '));
        if (empty($site)) {
            return $output->writeln("<error>Please enter a site</error>");
        }

        $section = $helper->ask($input, $output, new Question('Please enter the section (as outputted on IRC): '));
        if (empty($section)) {
            return $output->writeln("<error>Please enter a section</error>");
        }

        $rlsname = $helper->ask($input, $output, new Question('Please enter the rlsname: '));
        if (empty($rlsname)) {
            return $output->writeln("<error>Please enter a rlsname</error>");
        }


        $validTags = $siteModel->findValidTags($site, $section, $rlsname);
        if (!sizeof($validTags)) {
            $output->writeln("<error>Found no tags (must have failed all triggers!)</error>");
        } else {
            $output->writeln("\nFound tags:");

            $table = new Table($output);
            $table
                        ->setHeaders(['Tag'])
                        ->setRows(array($validTags))
                    ;
            $table->render();
        }

        $tagOptions = $app['models']['settings']->get('tag_options');

        $rows = array();
        foreach ($validTags as $k => $t) {
            $output->writeln(sprintf("\nTesting %s:", $t));

            $rowData = array();
            $rowData[] = $t;

            // requirements
            if (isset($tagOptions->$t) and isset($tagOptions->$t->tag_requires)) {
                $passes = ReleaseName::passesRequirements($rlsname, $tagOptions->$t->tag_requires);
                if ($passes !== true) {
                    unset($validTags[$k]);
                    $rowData[] = '✖';
                } else {
                    $rowData[] = '✔';
                }
            }

            // skiplist
            if (isset($tagOptions->$t) and isset($tagOptions->$t->tag_skiplist)) {
                $passes = ReleaseName::passesRegexSkiplists($rlsname, $tagOptions->$t->tag_skiplist);
                if ($passes !== true) {
                    unset($validTags[$k]);
                    $rowData[] = '✖';
                } else {
                    $rowData[] = '✔';
                }
            }

            $rows[] = $rowData;
        }

        $table = new Table($output);
        $table
                    ->setHeaders(['Tag', 'Requirements', 'Skiplist'])
                    ->setRows($rows)
                ;
        $table->render();


        $validTags = array_values($validTags);

        $output->writeln("\nRemaining possible tags:");
        $table = new Table($output);
        $table
                    ->setHeaders(['Tag'])
                    ->setRows(array($validTags))
                ;
        $table->render();
        
        return 0;
    }
}
