<?php

namespace TRD\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use TRD\Utility\CBFTP;

class Tools implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        // simulator
        $controllers->match('simulator', function (Request $request) use ($app) {
            $settings = $app['models']['settings'];
            $tags = array_keys(get_object_vars($app['models']['settings']->get('tag_options')));
            sort($tags, SORT_NATURAL | SORT_FLAG_CASE);
            $tagChoices = array_combine($tags, $tags);

            $form = $app['form.factory']->createBuilder('form')
                ->add('bookmark', 'choice', array(
                    'constraints' => array(new Assert\NotBlank())
                    ,'choices' => $tagChoices
                ))
                ->add('rlsname', 'text', array(
                    'constraints' => new Assert\NotBlank(),
                    'attr' => array('placeholder' => 'e.g. Something.S01E03.BDRIP.x264-Group')
                ))
                ->add('use_cache', 'choice', array(
                  'choices' => array(
                    'Yes' => true,
                    'No' => false
                  ),
                  'choice_attr' => function ($val, $key, $index) {
                      return ['class' => 'form-check-input'];
                  },
                  'expanded' => true,
                  'label' => 'Use cache (if possible)?'
                  ,'choices_as_values' => true,
                  'data' => 1
                ));

            $form = $form->getForm();

            $form->handleRequest($request);

            $results = array();
            $data = null;
            if ($form->isValid()) {
                $data = $form->getData();
            } else {
                if (!empty($request->get('rlsname'))) {
                    $data = array(
                  'rlsname' => $request->get('rlsname')
                  ,'bookmark' => $request->get('bookmark')
                  ,'use_cache' => ($request->get('use_cache') == 0 ? false : true)
                );
                }
            }

            $result = $raceData = $raceDataClean = $raceImmutableData = $autoResponse = null;
            if ($data !== null) {
                $sites = new \TRD\Model\Sites();
                $siteList = array();
                foreach ($sites->getData() as $siteName => $info) {
                    $siteList[] = $siteName;
                }

                $race = new \TRD\Race\Race($app, (bool)$data['use_cache']);
                $race->addSites($siteList);
                $result = $race->race($data['bookmark'], $data['rlsname']);
                sort($result->invalidSites);

                $raceData = $result->data->all();
                $raceDataClean = $result->data->getCleanData();
                $raceImmutableData = $result->data->getImmutableData();

                if ($_ENV['AUTOTRADING_ENABLED']) {
                    $parser = new \TRD\Parser\Rules();

                    $autoResponse = $app['models']['autorules']->evaluate($result->chain, $result->data);
                    if ($autoResponse !== false) {

                              //$command = new \TRD\Processor\ProcessorResponseCommand('APPROVED');
                              //$db->executeQuery("UPDATE race SET started = 1 WHERE rlsname = ? AND bookmark = ?", array($rlsname, $tag));
                    }
                }
            }

            return $app['twig']->render('tools/simulator.twig', array(
                'form' => $form->createView()
                ,'results' => $result
                ,'data' => $raceData
                ,'cleanData' => $raceDataClean
                ,'immutableData' => $raceImmutableData
                    ,'autoResponse' => $autoResponse
            ));
        })->bind('tools.simulator')->method('GET|POST');

        $controllers->get('/tag_overview', function () use ($app) {
            $settings = $app['models']['settings'];
            $sites = $app['models']['sites'];

            $tags = array();
            foreach ($settings->get('tags') as $tag) {
                $tags[$tag] = array();
            }
            ksort($tags);

            foreach ($sites->getData() as $siteName => $siteInfo) {
                foreach ($siteInfo->sections as $sectionInfo) {
                    foreach ($sectionInfo->tags as $tag) {
                        $tags[$tag->tag][] = (!empty($sectionInfo->bnc) ? $sectionInfo->bnc : $siteName) . ' - ' . $sectionInfo->name;
                    }
                }
            }

            foreach ($tags as $k => $v) {
                sort($v);
                $tags[$k] = $v;
            }

            return $app['twig']->render('tools/tag_overview.twig', array(
            'tags' => $tags
          ));
        })->bind('tools.tag_overview');
        
        $controllers->get('/data_immutable', function (Request $request) use ($app) {
            $rows = $app['db']->fetchAll("
              SELECT * FROM data_cache WHERE data_immutable IS NOT NULL 
              ORDER BY k ASC
            ");
            
            foreach ($rows as $k => $row) {
                $immutable = unserialize($row['data_immutable']);
                $original = unserialize($row['data']);
                
                $rows[$k]['fixed'] = [];
                foreach ($immutable as $field => $value) {
                    $rows[$k]['fixed'][] = [
                      'field' => $field,
                      'from' => $original[$field],
                      'to' => $value
                  ];
                }
            }

            return $app['twig']->render('tools/data_immutable.twig', array(
              'rows' => $rows,
              'msg' => $request->get('msg')
          ));
        })->bind('tools.data_immutable');
        
        $controllers->get('/data_immutable_reset', function (Request $request) use ($app) {
            $k = $request->get('k');
          
            if (empty($k)) {
                return $app->redirect($app['url_generator']->generate('tools.data_immutable'));
            }
          
            $app['db']->update('data_cache', [
              'data_immutable' => null,
            ], ['k' => $request->get('k')]);

            return $app->redirect($app['url_generator']->generate('tools.data_immutable', array(
                'msg' => sprintf('Immutable data reset for key: %s', $k)
            )));
        })->bind('tools.data_immutable_reset');

        $controllers->get('/efficiency', function () {
        })->bind('tools.efficiency');
        
        $controllers->get('/cbftp/raw', function (Request $request) use ($app) {
            $host = $app['models']['settings']->get('cbftp_host');
            $port = $app['models']['settings']->get('cbftp_api_port');
            $password = $app['models']['settings']->get('cbftp_password');
            
            $missingSettings = false;
            if (empty($host) or empty($port) or empty($password)) {
                $missingSettings = true;
            }
            
            $formView = $results = null;
            if (!$missingSettings) {
                $form = $app['form.factory']->createBuilder('form', ['path' => '/'])
                ->add('siteList', 'text', array(
                    'attr' => array('placeholder' => 'e.g. A,B,C')
                    ,'required' => false
                ))
                ->add('command', 'text', array(
                    'constraints' => new Assert\NotBlank(),
                    'attr' => array('placeholder' => 'e.g. site stat')
                ))
                ->add('path', 'text', array(
                    'constraints' => new Assert\NotBlank(),
                ));

                $form = $form->getForm();
                $formView = $form->createView();

                $form->handleRequest($request);

                $results = null;
                $data = null;
                if ($form->isValid()) {
                    $data = $form->getData();
                  
                    $sites = [];
                    if (!empty($data['siteList'])) {
                        $sites = explode(',', $data['siteList']);
                    }
                
                    $cb = new CBFTP($host, $port, $password);
                    $results = $cb->rawCapture($data['command'], $sites, $data['path']);
                }
            }
            
            return $app['twig']->render('tools/cbftp_raw.twig', array(
              'missingSettings' => $missingSettings,
              'results' => $results,
              'form' => $formView
            ));
        })->bind('tools.cbftp_raw')->method('GET|POST');
        
        $controllers->get('/cbftp/missing_sections', function (Request $request) use ($app) {
            $host = $app['models']['settings']->get('cbftp_host');
            $port = $app['models']['settings']->get('cbftp_api_port');
            $password = $app['models']['settings']->get('cbftp_password');
            
            $missingSettings = false;
            if (empty($host) or empty($port) or empty($password)) {
                $missingSettings = true;
            }
            
            $formView = $results = null;
            $ran = false;
            $standardResults = $ringResults = [];
            
            if (!$missingSettings) {
                $form = $app['form.factory']->createBuilder('form');

                $form = $form->getForm();
                $formView = $form->createView();

                $form->handleRequest($request);

                $results = null;
                $data = null;
                
                if ($form->isValid()) {
                    $ran = true;
                    $cb = new CBFTP($host, $port, $password);
                    
                    $sites = $app['models']['sites']->getSitesArray();
                    foreach ($sites as $siteName) {
                        $site = $app['models']['sites']->getSite($siteName);

                        if (!empty($site) and isset($site->sections)) {
                            $isRing = $app['models']['sites']->isRing($siteName);
                            if (!$isRing) {
                                $siteInfo = $cb->getSiteInfo($siteName);
                                if (!empty($siteInfo)) {
                                    foreach ($site->sections as $sectionInfo) {
                                        foreach ($sectionInfo->tags as $tagInfo) {
                                            $tagExists = CBFTP::siteHasSection($siteInfo['sections'], $tagInfo->tag);
                                            if (!$tagExists) {
                                                $standardResults["$siteName"][] = $tagInfo->tag;
                                            }
                                        }
                                    }
                                }
                            } else {
                                $ringResults[$siteName] = [];
                              
                                // have to treat rings a bit differently
                                $bncMap = [];
                                $bncTags = [];
                                foreach ($site->sections as $sectionInfo) {
                                    if (!empty($sectionInfo->bnc)) {
                                        $bnc = $sectionInfo->bnc;
                                        if (!isset($bncTags[$bnc])) {
                                            $bncTags[$bnc] = [];
                                        }
                                        foreach ($sectionInfo->tags as $tagInfo) {
                                            $bncTags[$bnc][] = $tagInfo->tag;
                                        }
                                        $ringResults[$siteName][$bnc] = [];
                                    }
                                }
                                
                                // let's actually do the checks now
                                foreach ($bncTags as $bnc => $tags) {
                                    $siteInfo = $cb->getSiteInfo($bnc);
                                    if (!empty($siteInfo)) {
                                        foreach ($tags as $tag) {
                                            $tagExists = CBFTP::siteHasSection($siteInfo['sections'], $tag);
                                            if (!$tagExists) {
                                                $ringResults["$siteName"]["$bnc"][] = $tag;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
                                  
            return $app['twig']->render('tools/cbftp_missing_sections.twig', array(
              'missingSettings' => $missingSettings,
              'ran' => $ran,
              'standardResults' => $standardResults,
              'ringResults' => $ringResults,
              'form' => $formView
            ));
        })->bind('tools.cbftp_missing_sections')->method('GET|POST');
        
        // default route
        $controllers->get('/tag_finder', function (Request $request) use ($app) {
            $siteChoices = [];
            $sites = $app['models']['sites']->getData();
            foreach ($sites as $siteName => $site) {
                $siteChoices[$siteName] = $siteName;
            }
          
            $form = $app['form.factory']->createBuilder('form')
              ->add('site', 'choice', array(
                  'constraints' => array(new Assert\NotBlank())
                  ,'choices' => $siteChoices
              ))
              ->add('announce_string', 'text', array(
                  'constraints' => new Assert\NotBlank(),
                  'attr' => array('placeholder' => 'e.g. New dir in GAMES: Rlsname')
              ));

            $form = $form->getForm();

            $form->handleRequest($request);

            $results = array();
            $data = null;
            if ($form->isValid()) {
                $data = $form->getData();
            }

            return $app['twig']->render('tools/tag_finder.twig', array(
              'form' => $form->createView()
          ));
        })->bind('tools.tag_finder')->method('GET|POST');

        // default route
        $controllers->get('/', function () use ($app) {
            return $app['twig']->render('tools/index.twig', array(

            ));
        })->bind('tools');

        return $controllers;
    }
}
