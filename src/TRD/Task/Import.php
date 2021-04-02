<?php

namespace TRD\Task;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

require_once(__DIR__ . '/../../../vendor/autoload.php');

class Import extends Command
{
    protected function configure()
    {
        $this->setName('trd:import')
            ->setDescription('Imports from some shit nTrader *.dat files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = require_once(__DIR__ . '/../../../app/bootstrap_console.php');

        $output->writeln("<info>Beginning import</info>");

        $sites = array();

        // import sites + site strings
        $data = file(__DIR__ . '/../../../../data/nt-site-string-lookup.dat');
        $lastSite = null;
        $lastString = null;
        foreach ($data as $c => $line) {
            $line = trim($line);

            // site
            if ($c % 2 == 0) {
                $bits = explode('-', $line);
                $lastSite = $bits[0];
                $lastString = implode('-', array_slice($bits, 1));
            } // site string
            else {
                if (!empty($line)) {
                    $sites["$lastSite"]['irc']['strings']["$lastString"] = $line;
                }
            }
            $c++;
        }

        // import site settings
        $data = file(__DIR__ . '/../../../../data/nt-site-settings.dat');
        $lastSite = null;
        $lastString = null;
        foreach ($data as $c => $line) {
            $line = trim($line);

            // site
            if ($c % 2 == 0) {
                $bits = explode('-', $line);
                $lastSite = $bits[0];
                $lastString = implode('-', array_slice($bits, 1));
            } // site string
            else {
                if (!empty($line)) {
                    $sites["$lastSite"]["$lastString"] = $line;
                }
            }
            $c++;
        }

        // import site chans
        $data = file(__DIR__ . '/../../../../data/nt-site-chan-lookup.dat');
        $lastSite = null;
        $lastString = null;
        foreach ($data as $c => $line) {
            $line = trim($line);

            // site
            if ($c % 2 == 0) {
                $lastSite = $line;
            } // site string
            else {
                if (!empty($line)) {
                    $sites["$lastSite"]['irc']['channel'] = $line;
                }
            }
            $c++;
        }

        // import site chan keys
        $data = file(__DIR__ . '/../../../../data/nt-site-chan-key-lookup.dat');
        $lastSite = null;
        $lastString = null;
        foreach ($data as $c => $line) {
            $line = trim($line);

            // site
            if ($c % 2 == 0) {
                $lastSite = $line;
            } // site string
            else {
                if (!empty($line)) {
                    $sites["$lastSite"]['irc']['channel_key'] = $line;
                }
            }
            $c++;
        }

        // import site chan bots
        $data = file(__DIR__ . '/../../../../data/nt-site-bot-lookup.dat');
        $lastSite = null;
        $lastString = null;
        foreach ($data as $c => $line) {
            $line = trim($line);

            // site
            if ($c % 2 == 0) {
                $lastSite = $line;
            } // site string
            else {
                if (!empty($line)) {
                    $sites["$lastSite"]['irc']['bot'] = $line;
                }
            }
            $c++;
        }


        $db = new \PDO('sqlite:/' . __DIR__ . '/../../../../data/nTrader.db');

        // import bncs
        $sth = $db->prepare("SELECT * FROM site_bnc");
        $sth->execute();
        $rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $sites[$row['site']]['bncs'][] = $row['bnc'];
        }

        // import sections
        $sth = $db->prepare("SELECT * FROM section");
        $sth->execute();
        $rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $sites[$row['site']]['sections'][] = array(
                'name' => $row['section']
                ,'pretime' => $row['pretime']
                ,'bnc' => $row['bnc']
            );
        }

        // import tags
        $sth = $db->prepare("SELECT * FROM section_tag");
        $sth->execute();
        $rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $sections = $sites[$row['site']]['sections'];

            // find the right section
            foreach ($sections as $k => $section) {
                if ($section['name'] == $row['section']) {
                    $sites[$row['site']]['sections'][$k]['tags'][] = array(
                        'tag' => $row['tag']
                        ,'trigger' => $row['trigger']
                    );
                }
            }
        }

        $output->writeln("Found <info>" . sizeof($sites) . "</info> sites");

        file_put_contents(__DIR__ . '/../../../../data/sites.json', json_encode($sites, JSON_PRETTY_PRINT));

        //$database = \eden('sqlite', );

        //var_dump($database->query('SELECT * FROM bnc'));
        
        return 0;
    }
}
