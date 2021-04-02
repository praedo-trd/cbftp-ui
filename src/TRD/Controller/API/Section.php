<?php

namespace TRD\Controller\API;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Section implements ControllerProviderInterface
{
    private $settings;
    private $sites;

    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        $controllers->get('', function () use ($app) {
            $settings = $app['models']['settings'];
            $tags = array_keys(get_object_vars($settings->get('tag_options')));
            sort($tags, SORT_NATURAL | SORT_FLAG_CASE);
            return $app->json($tags);
        });

        return $controllers;
    }
}
