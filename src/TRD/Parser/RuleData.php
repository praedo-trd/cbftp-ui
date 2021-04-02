<?php

namespace TRD\Parser;

class RuleData
{
    private $data = array();

    public function __construct()
    {
    }

    public function set($key, $val)
    {
        $val = $val === null ? '' : $val; // let's not accept null
        $this->data[$key] = $val;
    }

    public function setData($namespace, $data)
    {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $this->set($namespace . '.' . $k, $v);
            }
        }
    }

    public function get($key)
    {
        return $this->data[$key];
    }

    public function has($key)
    {
        return isset($this->data[$key]);
    }

    public function remove($key)
    {
        unset($this->data[$key]);
    }

    public function all()
    {
        return $this->data;
    }
}
