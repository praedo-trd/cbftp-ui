<?php

namespace TRD\Task;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

require_once(__DIR__ . '/../../../vendor/autoload.php');
require_once(__DIR__ . '/../../../app/env.php');

class Doctor extends Command
{
    protected function configure()
    {
        $this->setName('trd:doctor')
            ->setDescription('Checks config files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = require_once(__DIR__ . '/../../../app/bootstrap_console.php');

        $output->writeln("");
        $output->writeln("<comment>Checking integrity of config files:</comment>");

        $iterator = new \GlobIterator($_ENV['DATA_PATH'] . '/*.json');
        foreach ($iterator as $entry) {
            $json = json_decode(file_get_contents($_ENV['DATA_PATH'] . '/' . $entry->getFilename()));
            if ($json === null) {
                $output->writeln(sprintf("<error>Fail</error> %s is invalid JSON", $entry->getFilename()));
            } else {
                $output->writeln(sprintf("<info>Success</info> %s is valid JSON", $entry->getFilename()));
            }
        }

        /*
        $output->writeln("");
        $output->writeln("<comment>Checking all sites have required basic keys:</comment>");
        $json = json_decode(file_get_contents($_ENV['DATA_PATH'] . '/sites.json'), true);
        foreach($json AS $siteName => $siteInfo) {
            $badShit = array();
            if(!isset($siteInfo['irc'])) {
                $badShit[] = 'Missing IRC key';
            }
            if(!isset($siteInfo['enabled'])) {
                $badShit[] = 'Missing enabled key';
            }
            if(!isset($siteInfo['sections'])) {
                $badShit[] = 'Missing sections key';
            }
            if(!isset($siteInfo['affils'])) {
                $badShit[] = 'Missing affils key';
            }
            if(sizeof($badShit) > 0) {
                $output->writeln(sprintf("<error>Fail</error> %s has error: %s", $siteName, implode(', ', $badShit)));
            }
            else {
                $output->writeln(sprintf("<info>Success</info> %s has good config", $siteName));
            }
        }
        */

        /*
        $output->writeln("");
        $output->writeln("<comment>Checking all sites have required section keys:</comment>");
        foreach($json AS $siteName => $siteInfo) {

            $badShit = array();

            foreach($siteInfo['sections'] AS $section) {
                if(!array_key_exists('bnc', $section)) {
                    $badShit[] = 'Missing bnc key for section: ' . $section['name'];
                }
                if(!isset($section['tags'])) {
                    $badShit[] = 'Missing tags key for section: ' . $section['name'];
                }
                if(!isset($section['skiplists'])) {
                    $badShit[] = 'Missing skiplists key for section: ' . $section['name'];
                }
                if(!isset($section['rules'])) {
                    $badShit[] = 'Missing rules key for section: ' . $section['name'];
                }
            }

            if(sizeof($badShit) > 0) {
                $output->writeln(sprintf("<error>Fail</error> %s has error: %s", $siteName, implode(', ', $badShit)));
            }
            else {
                $output->writeln(sprintf("<info>Success</info> %s has good section config", $siteName));
            }
        }
        */

        $output->writeln("");

        // validate regexes O_o
        $dir = new \DirectoryIterator($_ENV['DATA_PATH'] . '/sites');
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                $filename = $fileinfo->getFilename();

                $output->writeln("Checking site <info>$filename</info> for invalid stiff...");

                $json = json_decode(file_get_contents($_ENV['DATA_PATH'] . '/sites/' . $filename), true);

                // channel
                if (isset($json['irc']['channel']) and !empty($json['irc']['channel'])) {
                    if ($error = invalidregex($json['irc']['channel'])) {
                        $output->writeln("<error>Bad string: irc.channel - $error</error>");
                    }
                }

                // bot
                if (isset($json['irc']['bot']) and !empty($json['irc']['bot'])) {
                    if ($error = invalidregex($json['irc']['bot'])) {
                        $output->writeln("<error>Bad string: irc.bot - $error</error>");
                    }
                }

                $strings = $json['irc']['strings'];

                // new string
                if (isset($strings['newstring-isregex']) and $strings['newstring-isregex']) {
                    if ($error = invalidregex($strings['newstring'])) {
                        $output->writeln("<error>Bad string: irc.strings.newstring - $error</error>");
                    }
                }

                // end string
                if (isset($strings['endstring-isregex']) and $strings['endstring-isregex']) {
                    if ($error = invalidregex($strings['endstring'])) {
                        $output->writeln("<error>Bad string: irc.strings.endstring - $error</error>");
                    }
                }

                if (isset($strings['prestring-isregex']) and $strings['prestring-isregex']) {
                    if ($error = invalidregex($strings['prestring'])) {
                        $output->writeln("<error>Bad string: irc.strings.prestring - $error</error>");
                    }
                }

                // sections
                foreach ($json['sections'] as $section) {
                    if (!isset($section['name'])) {
                        $output->writeln("<error>Bad section found - Missing section name property</error>");
                    }
                    $name = $section['name'];
                    if (!isset($section['tags'])) {
                        $output->writeln("<error>Bad section: sections.$name.tags - Missing section tags property</error>");
                    }
                  
                    
                    foreach ($section['tags'] as $tag) {
                        if ($error = invalidRegex($tag['trigger'])) {
                            $output->writeln("<error>Bad section tag trigger: sections.$name.$tag[tag] - $error</error>");
                        }
                    }
                }
            }
        }

        $output->writeln("Checking <info>skiplists.json</info> for invalid regexes...");
        $json = json_decode(file_get_contents($_ENV['DATA_PATH'] . '/skiplists.json'), true);
        foreach ($json as $name => $item) {
            if (isset($item['regex'])) {
                $regexes = explode("\n", $item['regex']);
                foreach ($regexes as $regex) {
                    if ($error = invalidRegex($regex)) {
                        $output->writeln("<error>Bad regex: $name - $error - $regex</error>");
                    }
                }
            }
        }

        $output->writeln("Checking <info>settings.json</info> for invalid regexes...");
        $json = json_decode(file_get_contents($_ENV['DATA_PATH'] . '/settings.json'), true);
        foreach ($json['tag_options'] as $tag => $options) {
            if (isset($options['tag_requires'])) {
                foreach ($options['tag_requires'] as $regex) {
                    if ($error = invalidRegex($regex)) {
                        $output->writeln("<error>Bad regex in tag_requires for: $tag (Regex: $regex) - $error</error>");
                    }
                }
            }
            if (isset($options['tag_skiplist'])) {
                foreach ($options['tag_skiplist'] as $regex) {
                    if (!empty($regex) and $error = invalidRegex($regex)) {
                        $output->writeln("<error>Bad regex in tag_skiplist for: $tag <warning>Regex: $regex</warning>- $error</error>");
                    }
                }
            }
        }
        
        return 0;
    }
}

function invalidRegex($regex)
{
    $old_error = error_reporting(0);
    if (preg_match($regex, '') !== false) {
        error_reporting($old_error);
        return '';
    }


    $errors = array(
        PREG_NO_ERROR               => 'Code 0 : No errors',
        PREG_INTERNAL_ERROR         => 'Code 1 : There was an internal PCRE error',
        PREG_BACKTRACK_LIMIT_ERROR  => 'Code 2 : Backtrack limit was exhausted',
        PREG_RECURSION_LIMIT_ERROR  => 'Code 3 : Recursion limit was exhausted',
        PREG_BAD_UTF8_ERROR         => 'Code 4 : The offset didn\'t correspond to the begin of a valid UTF-8 code point',
        PREG_BAD_UTF8_OFFSET_ERROR  => 'Code 5 : Malformed UTF-8 data',
    );

    $error = $errors[preg_last_error()];
    $errorLines = error_get_last();
    error_reporting($old_error);
    return $errorLines['message'];
}
