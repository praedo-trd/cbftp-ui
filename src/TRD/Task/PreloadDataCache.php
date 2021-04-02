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

class PreloadDataCache extends Command
{
    protected function configure()
    {
        $this->setName('trd:preload_data')
            ->setDescription('Uses third-party sources to try and pre-cache data provider information')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = require_once(__DIR__ . '/../../../app/bootstrap_console.php');

        $output->writeln("<info>Checking for upcoming movies</info>");

        $style = new \Symfony\Component\Console\Formatter\OutputFormatterStyle('red', 'default', array());
        $output->getFormatter()->setStyle('red', $style);

        $calendars = array();

        $now = new \DateTime('now');
        $calendars[] = array(
            'month' => $now->format('n')
            ,'year' => $now->format('Y')
        );

        $now->modify('first day of next month');
        $calendars[] = array(
            'month' => $now->format('n')
            ,'year' => $now->format('Y')
        );

        foreach ($calendars as $calendar) {
            $html = DataProvider::load(sprintf('http://www.blu-ray.com/movies/releasedates.php?year=%d&month=%d', $calendar['year'], $calendar['month']));

            $output->writeln(sprintf("<comment>Processing %d-%d", $calendar['year'], $calendar['month']) . '</comment>');

            preg_match_all('/movies\[\d+\].*?{(.*?)}/is', $html, $newMovies);
            if (sizeof($newMovies[0]) > 0) {
                $movies = array();

                foreach ($newMovies[1] as $newMovieJSON) {
                    preg_match_all('/([a-z-_]+):\s+(\'.*?\'|\d+)/i', $newMovieJSON, $tokens);
                    $map = array();
                    foreach ($tokens[1] as $k => $v) {
                        $value = $tokens[2][$k];
                        $map[$v] = $tokens[2][$k];
                        if (substr($value, 0, 1) == "'") {
                            $map["$v"] = substr($tokens[2][$k], 1, -1);
                        }
                    }

                    $movies[] = $map;
                }

                $checked = array();
                foreach ($movies as $movie) {
                    $url = sprintf('https://www.blu-ray.com/movies/%s-Blu-ray/%d/', $movie['title_keywords'], $movie['id']);

                    $html = DataProvider::load($url);
                    if (preg_match('/<a.*?id="imdb_icon".*?href="(https:\/\/www\.imdb\.com\/title\/(tt\d+)\/)" target="parent">/is', $html, $imdburl)) {
                        $imdb = new IMDBDataProvider($app);
                        $data = $imdb->extractDataFromIMDBId($imdburl[2]);

                        $title = trim(ReleaseName::titleToRlsname($data['title']) . ' ' . $data['year']);
                        if (!empty($title) and !isset($checked["$title"])) {
                            $checked["$title"] = array();

                            if (!empty($data['id']) and !$data['series']) {
                                $imdb->save($title, $data);
                                $output->writeln('(<info>' . "\xe2\x9c\x94" . '</info>) ' . $title);
                            } else {
                                $output->writeln('(<red>x</red>) ' . $title . ($data['series'] ? ' (TV)' : ''));
                            }
                        }
                    }

                    sleep(1);
                }
            }
        }
        return 0;
    }
}
