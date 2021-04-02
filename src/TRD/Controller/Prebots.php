<?php

namespace TRD\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

class Prebots implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        // simulator
        $controllers->match('/list', function (Request $request) use ($app) {
            return $app['twig']->render('prebots/list.twig', array());
        })->bind('prebots.list')->method('GET');

        return $controllers;
    }
}
