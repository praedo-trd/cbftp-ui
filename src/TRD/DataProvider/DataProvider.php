<?php

namespace TRD\DataProvider;

use TRD\Event\CacheMutatedEvent;
use TRD\DataProvider\DataProviderResponse;

abstract class DataProvider
{
    protected $container = null;
    protected $namespace = '';
    protected $debug = [];

    public function __construct($container)
    {
        $this->container = $container;
    }

    abstract public function getDefaults($existing = []);
    abstract protected static function getDeprecated();
    protected static function getGeneratedFields($data)
    {
        return [];
    }

    protected function lookup($key, $forceRefresh = false)
    {
        $key = strtolower($this->namespace . ':' . $key);
        $data = $this->container['db']->fetchAssoc("
          SELECT data, data_immutable, approved FROM data_cache WHERE `k` = ?
        ", array($key));

        if (!empty($data['data'])) {
            $existingData = unserialize($data['data']);
            if (!empty($data['data_immutable'])) {
                return new DataProviderResponse(true, $existingData, unserialize($data['data_immutable']), $data['approved'] == 1);
            }
            return new DataProviderResponse(true, $existingData, [], $data['approved'] == 1);
        }

        return new DataProviderResponse(false, null, []);
    }

    public function lookupId($id)
    {
        $data = $this->container['db']->fetchAssoc("SELECT data, data_immutable FROM data_cache WHERE id = ?", array($id));
        if (!empty($data['data'])) {
            $existingData = unserialize($data['data']);
            if (!empty($data['data_immutable'])) {
                return $this->mergeData($existingData, unserialize($data['immutable_data']));
            }
            return $existingData;
        }
        return false;
    }

    public function exists($key)
    {
        $key = $this->namespace . ':' . $key;
        $data = $this->container['db']->fetchColumn("SELECT data FROM data_cache WHERE `k` = ?", array($key));
        return !empty($data);
    }

    public function save($key, $data, $manuallyApproved = null)
    {
        $now = new \DateTime('now', new \DateTimeZone($_ENV['APP_TIMEZONE']));

        if (empty($key)) {
            return;
        }

        $k = $this->namespace . ':' . $key;

        // check
        $exists = $this->container['db']->fetchAssoc("SELECT k,data FROM data_cache WHERE k = ?", array($k));
        if (empty($exists)) {
            $row = array(
                'k' => strtolower($k)
                ,'data' => serialize($data)
                ,'namespace' => $this->namespace
                ,'id' => $data['id']
                ,'updated' => $now->format('Y-m-d H:i')
            );

            if (is_bool($manuallyApproved)) {
                $row['approved'] = ($manuallyApproved ? 1 : 0);
            }
            $this->container['db']->insert('data_cache', $row);

            $event = new CacheMutatedEvent($this->namespace, $key, null, $data);
        } else {
            $row = array(
                'data' => serialize($data)
                ,'id' => $data['id']
                ,'updated' => $now->format('Y-m-d H:i')
            );

            if (is_bool($manuallyApproved)) {
                $row['approved'] = ($manuallyApproved ? 1 : 0);
            }

            $this->container['db']->update('data_cache', $row, array(
                'k' => $k
            ));

            $event = new CacheMutatedEvent($this->namespace, $key, unserialize($exists['data']), $data);
        }
        $this->container['dispatcher']->dispatch(CacheMutatedEvent::NAME, $event);
    }

    public static function load($url, $userAgent = 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/36.0')
    {
        $options = array(
            CURLOPT_CUSTOMREQUEST  =>"GET",        //set request type post or get
            CURLOPT_POST           => false,        //set to GET
            CURLOPT_USERAGENT      => $userAgent, //set user agent
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_AUTOREFERER    => false,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 10,      // timeout on connect
            CURLOPT_TIMEOUT        => 10,      // timeout on response
            CURLOPT_HTTPHEADER => array(
                'Accept-language: en-us'
            ),
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_MAXREDIRS => 1,

        );

        $ch      = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $err     = curl_errno($ch);
        $errmsg  = curl_error($ch);
        $header  = curl_getinfo($ch);
        curl_close($ch);

        return $content;
    }

    protected function mergeData($current, $overrides, $addNew = false)
    {
        foreach ($overrides as $k => $v) {
            if (isset($current["$k"]) || $addNew) {
                $current["$k"] = $v;
            }
        }
        return $current;
    }
    
    public function getDebug()
    {
        return $this->debug;
    }
}
