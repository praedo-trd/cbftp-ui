<?php

namespace TRD\Task;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

require_once(__DIR__ . '/../../../vendor/autoload.php');
require_once(__DIR__ . '/../../../app/env.php');

class FakeRace extends Command
{
    protected function configure()
    {
        $this->setName('trd:fake_race')
            ->setDescription('Generate a fake race')
        ;

        $this->addOption(
            'random',
            null,
            InputOption::VALUE_OPTIONAL,
            'Should we generate a random release name?',
            "0"
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $useRandom = (bool)$input->getOption('random');

        $app = require_once(__DIR__ . '/../../../app/bootstrap_console.php');
        $helper = $this->getHelper('question');

        if (empty($_ENV['FAKE_RACE_ANNOUNCE'])) {
            $output->writeln("<info>This could be a lot quicker if you fill in the env FAKE_RACE_ANNOUNCE</info>");
            $output->writeln("");
            $output->writeln("<comment>Please enter a valid announce string from IRC for a games race</comment>");
            $output->writeln("<comment>Format: <channel> <nick> <msg></comment> (e.g. #channel botnick New release in &section: &release)");
            $output->writeln("<comment>Use &section and &release instead of the real deal</comment>");

            $ircString = $helper->ask($input, $output, new Question('', '[&section] &release started by fake/grp.'));

            if (empty($ircString)) {
                return $output->writeln("<error>Please enter an announce string</error>");
            }

            if (strpos($ircString, '&release') === false) {
                return $output->writeln("<error>Please enter an announce string containing the string &release</error>");
            }

            if (strpos($ircString, '&section') === false) {
                return $output->writeln("<error>Please enter an announce string containing the string &section</error>");
            }
        } else {
            $ircString = $_ENV['FAKE_RACE_ANNOUNCE'];
        }

        $section = $helper->ask($input, $output, new Question('Please enter the tag for your games section [GAMES]: ', 'GAMES'));
        if (empty($section)) {
            return $output->writeln("<error>Please enter a tag</error>");
        }

        if ($useRandom) {
            $group = $helper->ask($input, $output, new Question('Please enter the group for the randomnly generated game [RELOADED]: ', 'RELOADED'));
            if (empty($group)) {
                return $output->writeln("<error>Please enter a group</error>");
            }
            $rlsname = generateRandomString(15) . '-' . $group;
        } else {
            $rlsname = $helper->ask($input, $output, new Question('Please enter your rlsname: '));
            if (empty($rlsname)) {
                return $output->writeln("<error>Please enter a rlsname</error>");
            }
        }

        $msg = sprintf("IRC %s", str_replace(array('&release', '&section'), array($rlsname, $section), $ircString)) . "\n";
        $output->writeln("<comment>Sending message: </comment> $msg");

        $client = stream_socket_client(sprintf("tcp://%s:%s", $_ENV['SERVER_HOST'], $_ENV['SERVER_PORT']), $errno, $errorMessage);
        if ($client === false) {
            throw new \Exception("Failed to connect: $errorMessage");
        }

        fwrite($client, $msg);
        fclose($client);
        
        return 0;
    }
}

function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
