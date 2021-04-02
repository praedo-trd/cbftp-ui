<?php

namespace TRD\Model;

abstract class Model
{
    protected $data = array();
    protected $path = null;
    protected $name = null;
    protected $format = 'json';
    
    protected $cache = null;

    protected $refreshInterval = 0;
    protected $lastRefreshed = null;
    protected $needsRefresh = false;

    public function __construct($cache = null)
    {
        $this->cache = $cache;
        $this->path = $_ENV['DATA_PATH'] . '/' . $this->name . '.json';
        $this->load($this->path);
        $this->lastRefreshed = time();
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function refresh()
    {
        if ($this->refreshInterval > 0) {
            if ((time() - $this->lastRefreshed > $this->refreshInterval) || $this->needsRefresh) {
                if ($this->cache !== null) {
                    $this->cache->delete('trd:models:' . $this->name);
                }
                $this->load($this->path);
                $this->lastRefreshed = time();
                $this->needsRefresh = false;
            }
        }
    }

    protected function load($path, $skipCache = false)
    {

    // try and get the data from the cache first if possible
        if ($this->cache !== null && !$skipCache) {
            $model = $this->cache->get('trd:models:' . $this->name);
            if (!empty($model)) {
                // var_dump(debug_backtrace());
                $this->data = json_decode($model);
                return;
            }
        }

        // pull it from the file system instead
        $this->data = json_decode(file_get_contents($path));
        if ($this->cache !== null) {
            $this->cache->set('trd:models:' . $this->name, json_encode($this->data));
        }
    }

    public function save()
    {
        if (empty($this->data)) {
            return;
        }
      
        // persist to disk
        $this->needsRefresh = true;
        file_put_contents($_ENV['DATA_PATH'] . '/' . $this->name . '.json', json_encode($this->data, JSON_PRETTY_PRINT));
        if ($this->cache !== null) {
            $this->cache->set('trd:models:' . $this->name, json_encode($this->data));
        }
    }
}
