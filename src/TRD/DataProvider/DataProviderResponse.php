<?php

namespace TRD\DataProvider;

class DataProviderResponse
{
    public $result = false;
    public $cleanData = null;
    public $dataImmutable;
    public $generatedFields = null;
    public $approved = false;
    public $debug = [];

    public function __construct($result, $cleanData, $dataImmutable = array(), $approved = false)
    {
        $this->result = $result;
        $this->cleanData = $cleanData;
        $this->dataImmutable = $dataImmutable;
        $this->approved = (bool)$approved;
    }

    public function getCleanData()
    {
        return $this->cleanData;
    }
    
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    public function getData()
    {
        $data = $this->mergeData($this->cleanData, $this->dataImmutable);
        if ($this->generatedFields !== null) {
            $data = $this->mergeData($data, $this->generatedFields, true);
        }
        return $data;
    }

    public function get($key)
    {
        $data = $this->getData();
        return isset($data[$key]) ? $data[$key] : null;
    }

    public function getImmutableData()
    {
        return $this->dataImmutable;
    }

    private function mergeData($current, $overrides, $addNew = false)
    {
        if (!empty($overrides)) {
            foreach ($overrides as $k => $v) {
                if (array_key_exists($k, $current) || $addNew) {
                    $current["$k"] = $v;
                }
            }
        }
        return $current;
    }
    
    public function setGeneratedFields($fields)
    {
        return $this->generatedFields = $fields;
    }
}
