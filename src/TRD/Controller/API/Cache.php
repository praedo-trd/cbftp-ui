<?php

namespace TRD\Controller\API;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Cache implements ControllerProviderInterface
{
    private $settings;
    private $sites;

    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        $controllers->before(function () {
            $this->sites = json_decode(file_get_contents($_ENV['DATA_PATH'] . '/sites.json'));
            $this->settings = json_decode(file_get_contents($_ENV['DATA_PATH'] . '/settings.json'), true);
        });

        $controllers->post('/approve', function (Request $request) use ($app) {
            $k = $request->get('k');
            $app['db']->update('data_cache', array('approved' => 1), array('k' => $k));
            return $app->json(array('success' => 1));
        });

        return $controllers;
    }
}
