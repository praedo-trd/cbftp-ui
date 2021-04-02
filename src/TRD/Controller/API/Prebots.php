<?php

namespace TRD\Controller\API;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Prebots implements ControllerProviderInterface
{
    private $settings;
    private $sites;

    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        $controllers->post('save', function (Request $request) use ($app) {
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $newData = json_decode($request->getContent());
                $prebotsModel = $app['models']['prebots'];
                $prebotsModel->setData($newData);
                $prebotsModel->save();
                return $app->json(array('success' => 1));
            }
            return $app->json(array('error' => 1));
        });

        $controllers->get('', function () use ($app) {
            $prebots = $app['models']['prebots'];
            return $app->json($prebots->getData());
        });

        return $controllers;
    }
}
