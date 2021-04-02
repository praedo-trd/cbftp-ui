<?php

namespace TRD\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

class Site implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        // default route
        $controllers->get('/list', function () use ($app) {

            // hacxxxxx
            $sites = json_decode(json_encode($app['models']['sites']->getData()), true);

            //ksort($sites);

            uksort($sites, function ($a, $b) use ($sites) {
                $ao = $sites["$a"];
                $bo = $sites["$b"];

                $rdiff = $bo['enabled'] - $ao['enabled'];
                if ($rdiff) {
                    return $rdiff;
                }
                return strcmp($a, $b);
            });

            $races = $app['db']->fetchAll("
                SELECT * FROM race ORDER BY created DESC LIMIT 10
            ");

            foreach ($sites as $siteName => $site) {
                $sites[$siteName]['untaggedSections'] = 0;
                foreach ($site['sections'] as $section) {
                    if (sizeof($section['tags']) === 0) {
                        $sites[$siteName]['untaggedSections']++;
                    }
                }
            }

            return $app['twig']->render('site/list.twig', array(
                'sites' => $sites
                ,'races' => $races
            ));
        })
            ->bind('site.list');

        $controllers->match('/{name}/edit', function (Request $request, $name) use ($app) {
            $sites = $app['models']['sites'];
            $site = $sites->getSite($name);
            $sections = $site->sections;
            usort($sections, function ($a, $b) {
                return strcmp($a->name, $b->name);
            });

            if (empty($site)) {
                return $app->abort(404, 'No such site');
            }

            $config = json_decode(file_get_contents($_ENV['DATA_PATH'] . '/settings.json'), true);

            $form = $app['form.factory']->createBuilder('form', array())
                ->add('enabled', 'checkbox', array(
                    'label' => 'Enabled?'
                ))
                ->getForm();

            $allSites = json_decode(json_encode($app['models']['sites']->getData()), true);
            uksort($allSites, function ($a, $b) use ($allSites) {
                $ao = $allSites["$a"];
                $bo = $allSites["$b"];

                $rdiff = $bo['enabled'] - $ao['enabled'];
                if ($rdiff) {
                    return $rdiff;
                }
                return strcmp($a, $b);
            });

            return $app['twig']->render('site/edit.twig', array(
                'siteName' => $name
                ,'site' => $site
                ,'sites' => $allSites
                ,'sections' => $sections
                ,'form' => $form->createView()
                ,'siteData' => json_encode($site)
                ,'tags' => $config['tags']
            ));
        })->method('GET|POST')->bind('site.edit');

        $controllers->match('/add', function (Request $request) use ($app) {
            $form = $app['form.factory']->createBuilder('form', array())
                ->add('name', 'text', array(
                    'constraints' => new Assert\NotBlank()
                ))->getForm();

            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();

                $sites = new \TRD\Model\Sites();
                $sites->addSite($data['name']);

                // redirect somewhere
                return $app->redirect($app['url_generator']->generate('site.list'));
            }

            return $app['twig']->render('site/add.twig', array(
                'form' => $form->createView()
            ));
        })->bind('site.add')->method('GET|POST');

        return $controllers;
    }
}
