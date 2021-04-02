<?php

namespace TRD\Model;

class Sections
{
    private $data = null;
    private $db = null;
    
    private $path = null;

    public function __construct()
    {
        $this->path = $_ENV['DATA_PATH'] . '/sections.json';
        $this->load();
    }

    public function getData()
    {
        return $this->data;
    }

    public function getSection($section)
    {
        foreach ($this->data as $s) {
            if ($s->name == $section) {
                return $s;
            }
        }
        return null;
    }

    public function getAllBookmarks($section)
    {
        $section = $this->getSection($section);
        $tags = array();
        $allStrings = array();
        if (!empty($section)) {
            $generator = $section->generator;
            foreach ($generator as $k => $bit) {
                $scope = $section->scope->{$bit};
                foreach ($scope as $scopeOption) {
                    $allStrings[$k][] = $scopeOption;
                }
            }
        }

        $tags = $this->_h($allStrings);

        return $tags;
    }

    public function generateBookmark($section, $data)
    {
        $section = $this->getSection($section);


        $bookmark = '';
        if (!empty($section)) {
            $generator = $section->generator;
            foreach ($generator as $k => $bit) {
                $bookmark .= $data->get($bit);
            }
        }
        return strtoupper($bookmark);
    }

    private function _g($input, $f)
    {
        if (count($input) == 1) {
            return $input[0];
        }
        $output = array();
        foreach ($input[0] as $i) {
            $src = $this->{$f}(array_slice($input, 1));
            foreach ($src as $e) {
                $output[] = strtoupper($i.$e);
            }
        }
        return $output;
    }

    private function _h($array)
    {
        return self::_g($array, "_h");
    }

    private function load()
    {
        $this->data = json_decode(file_get_contents($this->path));
    }

    private function save()
    {
        file_put_contents($this->path, json_encode($this->data, JSON_PRETTY_PRINT));
    }
}
