<?php

namespace TRD\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Pagerfanta\Adapter\DoctrineDbalAdapter;
use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Pagerfanta;
use Pagerfanta\View\TwitterBootstrap4View;
use TRD\Race\RaceResult;

function timeAgo($time_ago)
{
    $time_ago = strtotime($time_ago);
    $cur_time   = time();
    $time_elapsed   = $cur_time - $time_ago;
    $seconds    = $time_elapsed ;
    $minutes    = round($time_elapsed / 60);
    $hours      = round($time_elapsed / 3600);
    $days       = round($time_elapsed / 86400);
    $weeks      = round($time_elapsed / 604800);
    $months     = round($time_elapsed / 2600640);
    $years      = round($time_elapsed / 31207680);
    // Seconds
    if ($seconds <= 60) {
        return "just now";
    }
    //Minutes
    elseif ($minutes <=60) {
        if ($minutes==1) {
            return "one minute ago";
        } else {
            return "$minutes minutes ago";
        }
    }
    //Hours
    elseif ($hours <=24) {
        if ($hours==1) {
            return "an hour ago";
        } else {
            return "$hours hrs ago";
        }
    }
    //Days
    elseif ($days <= 7) {
        if ($days==1) {
            return "yesterday";
        } else {
            return "$days days ago";
        }
    }
    //Weeks
    elseif ($weeks <= 4.3) {
        if ($weeks==1) {
            return "a week ago";
        } else {
            return "$weeks weeks ago";
        }
    }
    //Months
    elseif ($months <=12) {
        if ($months==1) {
            return "a month ago";
        } else {
            return "$months months ago";
        }
    }
    //Years
    else {
        if ($years==1) {
            return "one year ago";
        } else {
            return "$years years ago";
        }
    }
}

class Race implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        // default route
        $controllers->get('/list', function (Request $request) use ($app) {
            $page = 1;
            if ($app['request']->get('page')) {
                $page = $app['request']->get('page');
            }

            $queryBuilder = new QueryBuilder($app['db']);
            $queryBuilder->select('r.*')->from('race', 'r')->orderBy('r.created', 'DESC');

            $tag = $app['request']->get('tag');
            if (!empty($tag)) {
                $queryBuilder->where('r.bookmark = :tag')->setParameter('tag', $app['request']->get('tag'));
            }

            $countQueryBuilderModifier = function ($queryBuilder) {
                $queryBuilder->select('COUNT(DISTINCT r.id) AS total_results')
                      ->setMaxResults(1);
            };

            $adapter = new DoctrineDbalAdapter($queryBuilder, $countQueryBuilderModifier);
            $pagerfanta = new Pagerfanta($adapter);
            $pagerfanta->setMaxPerPage(100);
            $pagerfanta->setCurrentPage($page);

            $races = $adapter->getSlice($pagerfanta->getCurrentPageOffsetStart(), $pagerfanta->getMaxPerPage());
            $races = $pagerfanta->getCurrentPageResults();

            // die;

            foreach ($races as $k => $row) {
                $races["$k"]['time_ago'] = timeAgo($row['created']);
                

                $races["$k"]['data_null'] = false;
                $races["$k"]['has_cache'] = false;
                $races["$k"]['is_catastrophe'] = false;
                $races["$k"]['duration'] = -1;
                $races["$k"]['dataLookupDuration'] = -1;
                
                $log = unserialize($races["$k"]['log']);
                if ($log instanceof \TRD\Race\RaceResult) {
                    if ($log->data !== null) {
                        $data = $log->data->all();
                        $races["$k"]['has_cache'] = (isset($data['tvmaze.url']) or isset($data['imdb.url']));
                    } else {
                        $races["$k"]['data_null'] = false;
                    }
                    $races["$k"]['is_catastrophe'] = sizeof($log->catastrophes) > 0;
                    $races["$k"]['duration'] = $log->getDuration();
                    $races["$k"]['dataLookupDuration'] = $log->getDataLookupDuration();
                }

                $races["$k"]['chain_info'] = array();
                if (!empty($races["$k"]['chain'])) {
                    $chain = explode(",", $races["$k"]['chain']);
                    $complete = explode(',', $races["$k"]['chain_complete']);
                    sort($chain);
                    foreach ($chain as $c) {
                        $races["$k"]['chain_info'][] = array(
                        'name' => $c,
                        'complete' => in_array($c, $complete)
                      );
                    }
                }
            }

            $tags = array_keys(get_object_vars($app['models']['settings']->get('tag_options')));
            sort($tags, SORT_NATURAL | SORT_FLAG_CASE);

            // data sources map
            $datasourceMap = array();
            foreach ($app['models']['settings']->get('tag_options') as $t => $options) {
                $datasourceMap[$t] = (bool)(isset($options->data_sources) && sizeof($options->data_sources) > 0);
            }

            $routeGenerator = function ($page) use ($app) {
                return $app['url_generator']->generate('race.list', array('page' => $page, 'tag' => $app['request']->get('tag')));
            };

            $view = new TwitterBootstrap4View();
            $options = array('proximity' => 3);
            $paginationHTML = $view->render($pagerfanta, $routeGenerator, $options);

            return $app['twig']->render('race/list.twig', array(
                'races' => $races
                ,'tag' => $tag
                ,'tags' => $tags
                ,'paginationHTML' => $paginationHTML
                ,'datasourceMap' => $datasourceMap
            ));
        })
            ->bind('race.list');

        $controllers->get('{id}/log', function ($id) use ($app) {
            $race = $app['db']->fetchAssoc("SELECT * FROM race WHERE id = ?", array($id));
            if (empty($race)) {
                return $app->abort(404, 'Race not found');
            }
            $race['log'] = unserialize($race['log']);
            sort($race['log']->invalidSites);

            return $app['twig']->render('race/view_log.twig', array(
            'race' => $race
            ,'data' => $race['log']->data->all()
            ,'duration' => $race['log']->getDuration()
            ,'dataLookupDuration' => $race['log']->getDataLookupDuration()
          ));
        })->bind('race.view_log');

        return $controllers;
    }
}
