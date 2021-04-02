<?php

namespace TRD\Controller\API;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class Doc implements ControllerProviderInterface
{
    private $settings;
    private $sites;

    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        $controllers->get('/parse/{path}', function (Request $request, $path) {
            $Parsedown = new \Parsedown();
            $realPath = __DIR__ . '/../../../../doc/' . $path . '.md';
            if (file_exists($realPath)) {
                return $Parsedown->text(file_get_contents($realPath));
            }
            return '';
        });

        return $controllers;
    }
}
