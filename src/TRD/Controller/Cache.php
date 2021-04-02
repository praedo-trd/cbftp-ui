<?php

namespace TRD\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TRD\DataProvider\TVMazeDataProvider;
use TRD\DataProvider\IMDBDataProvider;

use Symfony\Component\Validator\Constraints as Assert;

class Cache implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        // default route
        $controllers->get('list', function (Request $request) use ($app) {
            $approved = 1;
            if (empty($request->get('approved'))) {
                $approved = 0;
            }

            $params = array($approved);

            $totals = $app['db']->fetchAll("
              SELECT COUNT(*) as total, approved
              FROM data_cache
              WHERE namespace='tvmaze'
              GROUP BY approved
            ");
            $realTotals = array();
            foreach ($totals as $row) {
                $realTotals[$row['approved']] = $row['total'];
            }

            $items = $app['db']->fetchAll("
              SELECT * FROM data_cache
              WHERE namespace = 'tvmaze' AND approved = ?
              ORDER BY k ASC
            ", $params);

            foreach ($items as $k => $row) {
                $items["$k"]['data'] = unserialize($row['data']);
            }

            return $app['twig']->render('cache/list.twig', array(
                'items' => $items,
                'approved' => $approved
                ,'totals' => $realTotals
            ));
        })
        ->bind('cache.list');

        return $controllers;
    }
}
