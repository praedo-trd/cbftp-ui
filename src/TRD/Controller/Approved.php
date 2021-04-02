<?php

namespace TRD\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Validator\Constraints as Assert;

class Approved implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        // default route
        $controllers->get('/list', function () use ($app) {
            $extra = '';
            $extraFields = array();
            $tag = $app['request']->get('tag');
            if (!empty($tag)) {
                $extra .= ' AND bookmark = ?';
                $extraFields[] = $tag;
            }

            $show = $app['request']->get('show');
            if (empty($show)) {
                $show = 'active';
            }

            $activeCount = $app['db']->fetchColumn("
              SELECT COUNT(*) as total
              FROM approved AS a
              WHERE (expires >= NOW() AND (a.hits < a.maxlimit OR a.maxlimit = 0))
            ");
            $expiredCount = $app['db']->fetchColumn("
              SELECT COUNT(*) as total
              FROM approved AS a
              WHERE (expires < NOW() or (a.maxlimit > 0 and a.hits > 0 and a.hits >= a.maxlimit))
            ");

            switch ($show) {
                case 'active':
                    $extra .= ' AND (expires >= NOW() AND (a.hits < a.maxlimit OR a.maxlimit = 0))';
                break;

                case 'expired':
                    $extra .= ' AND (expires < NOW() or (a.maxlimit > 0 and a.hits >= a.maxlimit))';
                break;
            }

            $approved = $app['db']->fetchAll("
                SELECT
                    a.*,
                    IF(a.expires < now() OR (a.maxlimit > 0 and a.hits >= a.maxlimit), true, false) as expired
                FROM approved AS a
                WHERE 1 = 1 $extra
                ORDER BY expires DESC
            ", $extraFields);
            foreach ($approved as $k => $row) {
                //$races["$k"]['time_ago'] = timeAgo($row['created']);
            }

            return $app['twig']->render('approved/list.twig', array(
                'approved' => $approved
                ,'tag' => $tag
                ,'show' => $show
                ,'msg' => $app['request']->get('msg')
                ,'activeCount' => $activeCount
                ,'expiredCount' => $expiredCount
            ));
        })
            ->bind('approved.list');

        $updateApproved = function (Request $request, $id) use ($app) {
            $settings = new \TRD\Model\Settings();

            $tags = array_keys(get_object_vars($app['models']['settings']->get('tag_options')));
            sort($tags, SORT_NATURAL | SORT_FLAG_CASE);
            $tagChoices = array_combine($tags, $tags);

            $siteChoices = array();
            $sites = new \TRD\Model\Sites();
            $siteList = $sites->getData();
            foreach ($siteList as $siteName => $noop) {
                if ($noop->enabled) {
                    $siteChoices[$siteName] = $siteName;
                }
            }
            ksort($siteChoices);

            $data = $app['db']->fetchAssoc("SELECT * FROM approved WHERE id = ?", array($id));
            if (empty($data)) {
                $data = array(
                'maxlimit' => 1
                ,'type' => 'WILDCARD'
            );
            }

            if ($request->get('rlsname')) {
                $data['pattern'] = $request->get('rlsname');
            }
            if ($request->get('bookmark')) {
                $data['bookmark'] = $request->get('bookmark');
            }
            if ($request->get('chain')) {
                $data['chain'] = $request->get('chain');
            }

            $form = $app['form.factory']->createBuilder('form', $data)
                ->add('bookmark', 'choice', array(
                    'constraints' => array(new Assert\NotBlank())
                    ,'choices' => $tagChoices
                ))
                ->add('pattern', 'text', array(
                    'constraints' => new Assert\NotBlank(),
                    'attr' => array('placeholder' => 'e.g. Something.S01E*.BDRIP.x264-Group')
                ))
                ->add('type', 'choice', array(
                    'choices' => array('WILDCARD' => 'Wildcard', 'REGEX' => 'Regex'),
                    'expanded' => true,
                    'constraints' => new Assert\Choice(array('WILDCARD', 'REGEX')),
                ))
                ->add('chain', 'textarea', array(
                    'attr' => array(
                        'placeholder' => 'Although this is multi-line, don\'t add newlines. Seperate BNC names with commas'
                    )
                ))
                ->add('maxlimit', 'text', array(
                    'constraints' => new Assert\Type(array('type' => 'numeric'))
                    ,'attr' => array(
                        'data-help' => 'Set to 0 to allow unlimited'
                    )
                ));

            if ($id > 0) {
                $form->add('hits', 'text', array(
                    'attr' => array('disabled' => 'disabled')
                ));
            }

            $form->add('expires', 'datetime', array(
                    'data' => (
                        $id == 0 ?
                                new \DateTime('now +1day', new \DateTimeZone($_ENV['APP_TIMEZONE'])) :
                                new \DateTime($data['expires'], new \DateTimeZone($_ENV['APP_TIMEZONE']))
                    )
                ));

            $form = $form->getForm();

            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();

                // clean up
                unset($data['id'], $data['hits'], $data['created']);
                $data['expires'] = $data['expires']->format('Y-m-d H:i:s');

                $data['chain'] = str_replace(
                    array(' ', ';'),
                    array('',','),
                    $data['chain']
                );

                if ($id == 0) {
                    $app['db']->insert('approved', $data);
                } else {
                    $app['db']->update('approved', $data, array('id' => $id));
                }

                // redirect somewhere
                return $app->redirect($app['url_generator']->generate('approved.list', array(
                    'show' => 'active'
                )));
            }

            return $app['twig']->render('approved/update.twig', array(
                    'form' => $form->createView()
                    ,'id' => $id
                ));
        };

        $controllers->match('/{id}/edit', $updateApproved)->method('GET|POST')->bind('approved.edit');
        $controllers->match('/add', $updateApproved)->method('GET|POST')->bind('approved.add')->value('id', 0);

        $controllers->get('/{id}/delete', function (Request $request, $id) use ($app) {
            $app['db']->delete('approved', array('id' => $id));
            return $app->redirect($app['url_generator']->generate('approved.list', array('show' => 'active', 'msg' => 'Approval rule deleted successfully')));
        })->bind('approved.delete');
        
        $controllers->match('/deleteExpired', function (Request $request) use ($app) {
            $form = $app['form.factory']->createBuilder('form');
            $form = $form->getForm();
            
            $total = $app['db']->fetchColumn("SELECT COUNT(*) AS total FROM approved AS a WHERE (expires < NOW() or (a.maxlimit > 0 and a.hits >= a.maxlimit))");
            
            $form->handleRequest($request);

            if ($form->isValid()) {
                $app['db']->executeQuery("DELETE FROM approved WHERE (expires < NOW() or (maxlimit > 0 and hits >= maxlimit))");
                return $app->redirect($app['url_generator']->generate('approved.list', array('show' => 'expired', 'msg' => 'Expired approval rules deleted successfully')));
            }
            
            return $app['twig']->render('approved/delete_expired.twig', array(
                'form' => $form->createView(),
                'total' => $total
              ));
        })->method('GET|POST')->bind('approved.delete_expired');

        return $controllers;
    }
}
