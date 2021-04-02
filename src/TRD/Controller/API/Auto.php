<?php

namespace TRD\Controller\API;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Auto implements ControllerProviderInterface
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

        $controllers->post('/tv/approve', function (Request $request) use ($app) {
            $id = $request->get('id');
            $approved = $request->get('approved');
            $app['db']->update('auto_tv', array('approved' => $approved), array('id' => $id));
            return $app->json(array('success' => 1));
        });

        $controllers->post('/movies/approve', function (Request $request) use ($app) {
            $id = $request->get('id');
            $approved = $request->get('approved');
            $app['db']->update('auto_movies', array('approved' => $approved), array('id' => $id));
            return $app->json(array('success' => 1));
        });

        $controllers->get('/settings/{section}', function (Request $request, $section) use ($app) {
            $settings = $app['models']['settings'];
            $autoSettings = $settings->get('auto_settings');
            if (!empty($autoSettings) and isset($autoSettings->$section)) {
                return $app->json($autoSettings->$section);
            }
            return $app->json(array());
        });

        $controllers->post('/settings/{section}', function (Request $request, $section) use ($app) {
            $sections = explode(',', $request->get('allowed_classifications'));
            $countries = explode(',', $request->get('allowed_countries'));

            $settings = $app['models']['settings'];
            $existingSettings = $settings->getData();

            $existingSettings->auto_settings->$section->allowed_classifications = $sections;
            $existingSettings->auto_settings->$section->allowed_countries = $countries;
            $settings->setData($existingSettings);

            return $app->json(array('success' => 1));
        });

        return $controllers;
    }
}
