<?php

namespace TRD\Task;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use Symfony\Component\Validator\Exception\UnexpectedValueException;

require_once(__DIR__ . '/../../../vendor/autoload.php');
require_once(__DIR__ . '/../../../app/env.php');

class FakePretip extends Command
{
    protected function configure()
    {
        $this->setName('trd:fake_pretip')
            ->setDescription('Generate a fake pretip')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = require_once(__DIR__ . '/../../../app/bootstrap_console.php');
        $helper = $this->getHelper('question');

        $section = $helper->ask($input, $output, new Question('Please enter the tag for your pretip [GAMES]: ', 'GAMES'));
        if (empty($section)) {
            return $output->writeln("<error>Please enter a tag</error>");
        }

        $rlsname = $helper->ask($input, $output, new Question('Please enter the rlsname: '));
        if (empty($rlsname)) {
            return $output->writeln("<error>Please enter a rlsname</error>");
        }

        $msg = sprintf("PRETIP %s %s", $section, $rlsname) . "\n";
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
