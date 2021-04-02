<?php

namespace TRD\Controller\API;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Skiplist implements ControllerProviderInterface
{
    private $settings;
    private $sites;

    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        $controllers->before(function () {
            // $this->sites = json_decode(file_get_contents($_ENV['DATA_PATH'] . '/sites.json'));
            // $this->settings = json_decode(file_get_contents($_ENV['DATA_PATH'] . '/settings.json'), true);
        });

        $controllers->post('save', function (Request $request) use ($app) {
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $newData = json_decode($request->getContent());
                $skiplistsModel = $app['models']['skiplists'];
                $skiplistsModel->setData($newData);
                $skiplistsModel->save();
                return $app->json(array('success' => 1));
            }
            return $app->json(array('error' => 1));
        });

        $controllers->get('', function () use ($app) {
            $skiplists = $app['models']['skiplists'];
            return $app->json($skiplists->getData());
        });

        return $controllers;
    }
}
