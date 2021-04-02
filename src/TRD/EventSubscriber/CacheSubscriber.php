<?php

namespace TRD\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

use TRD\Event\CacheMutatedEvent;

class CacheSubscriber implements EventSubscriberInterface
{
    private $container = null;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'cache.mutated' => array(
                array('updateAutoTV'),
                // array('sendEmails', 5)
            )
        );
    }

    public function updateAutoTV(CacheMutatedEvent $event)
    {
        $namespace = $event->getNamespace();
        $key = $event->getKey();
        $before = $event->getBefore();
        $after = $event->getAfter();

        $settings = $this->container['models']['settings'];
        $autoSettings = $settings->get('auto_settings');
        if (isset($autoSettings->hdtv)) {
            if ($namespace === 'tvmaze') {
                if (!in_array($after['classification'], $autoSettings->hdtv->allowed_classifications)) {
                    return;
                }
                if (!in_array($after['country'], $autoSettings->hdtv->allowed_countries)) {
                    return;
                }
            }
        }

        if ($namespace === 'tvmaze' and $after['status'] !== 'Ended' and $after['daily'] === false) {
            $key = str_replace(' ', '.', $key);

            $now = new \DateTime('now', new \DateTimeZone($_ENV['APP_TIMEZONE']));
            if ($before === null && $after['total_seasons'] > 0) { // new
                try {
                    $this->container['db']->insert('auto_tv', array(
                      'title' => $key,
                      'season' => $after['total_seasons'],
                      'approved' => -1,
                      'created' => $now->format('Y-m-d H:i')
                  ));
                } catch (\Exception $e) {
                }
            } else { // update
                if ($before['total_seasons'] != $after['total_seasons'] && $after['total_seasons'] > $before['total_seasons'] && $after['total_seasons'] > 0) {
                    try {
                        $this->container['db']->insert('auto_tv', array(
                          'title' => $key,
                          'season' => $after['total_seasons'],
                          'approved' => -1,
                          'created' => $now->format('Y-m-d H:i')
                      ));
                    } catch (\Exception $e) {
                    }
                }
            }
        }
    }
}
