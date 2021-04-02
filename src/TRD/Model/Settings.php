<?php

namespace TRD\Model;

class Settings extends \TRD\Model\Model
{
    protected $name = 'settings';
    protected $refreshInterval = 60;

    public function getSite($siteName)
    {
        return $this->data->$siteName;
    }

    public function setData($newSettings)
    {
        $this->data = $newSettings;
        $this->save();
    }

    public function exists($key)
    {
        return property_exists($this->data, $key);
    }

    public function get($key)
    {
        if (property_exists($this->data, $key)) {
            return $this->data->$key;
        }

        return;
    }

    public function addSettings($key, $val)
    {
        if (!isset($this->data[$key])) {
            $this->data->{$key} = $val;
            $this->save();

            return true;
        }

        return false;
    }
}
