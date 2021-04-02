<?php

namespace TRD\Controller\API;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use TRD\Utility\AnnounceString;

class Site implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        $controllers->post('/{name}/save', function (Request $request, $name) use ($app) {
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $newData = json_decode($request->getContent());
                $siteModel = $app['models']['sites'];
                $siteModel->replaceSite($name, $newData);
                $siteModel->save();
                return $app->json(array('success' => 1));
            }
            return $app->json(array('error' => 1));
        });

        $controllers->post('/{name}/testString', function (Request $request, $name) use ($app) {
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $site = $app['models']['sites']->getSite($name);

                $postData = json_decode($request->getContent(), true);

                $testString = $postData['testString'];
                $keyBits = explode('.', $postData['key']);
                $endBit = array_pop($keyBits);

                $extraction = \TRD\Utility\IRCExtractor::extract($site->irc->strings, array($endBit), $testString);
                $section = $extraction['section'];
                $rlsname = $extraction['rlsname'];

                return $app->json(array(
                  'matched' => $section !== null || $rlsname !== null,
                  'section' => $section, 'rlsname' => $rlsname
                ));
            }
            return $app->json(array('error' => 1));
        });

        $controllers->get('/{name}', function ($name) use ($app) {
            $sites = $app['models']['sites'];

            return $app->json($sites->getSite($name));
        });

        return $controllers;
    }
}
