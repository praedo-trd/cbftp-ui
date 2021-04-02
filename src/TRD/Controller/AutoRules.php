<?php

namespace TRD\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TRD\DataProvider\TVMazeDataProvider;
use TRD\DataProvider\IMDBDataProvider;

use Symfony\Component\Validator\Constraints as Assert;

class AutoRules implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        // default route
        $controllers->get('/', function (Request $request) use ($app) {
            return $app['twig']->render('autorules/list.twig', array(

            ));
        })
        ->bind('autorules.list');

        return $controllers;
    }
}
