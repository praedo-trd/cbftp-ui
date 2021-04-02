<?php

namespace TRD\Controller\API;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use TRD\Utility\AnnounceString;

class Settings implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        $controllers->post('/save', function (Request $request) use ($app) {
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $newData = json_decode($request->getContent());
                $settingsModel = $app['models']['settings'];
                $settingsModel->setData($newData);
                $settingsModel->save();
                return $app->json(array('success' => 1));
            }
            return $app->json(array('error' => 1));
        });

        $controllers->get('', function () use ($app) {
            $settings = $app['models']['settings']->getData();
            return $app->json($settings);
        });

        return $controllers;
    }
}
