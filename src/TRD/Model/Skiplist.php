<?php

namespace TRD\Model;

class Skiplist extends \TRD\Model\Model
{
    protected $name = 'skiplists';
    protected $refreshInterval = 60;

    public function getSkiplist($name)
    {
        return isset($this->data->$name) ? $this->data->$name : null;
    }
    
    public function getSkiplistRegex($name)
    {
        $skiplist  = $this->getSkiplist($name);
        if ($skiplist && $skiplist->regex) {
            return $skiplist->regex;
        }
        return null;
    }

    public function addItem($name, $item)
    {
        if (substr_count($item, '*') > 0) {
            $found = false;
            foreach ($this->data->$name->items as $i) {
                if ($i == $item) {
                    $found = true;
                }
            }

            if (!$found) {
                $this->data->$name->items[] = $item;
                $this->save();
            }
        }
        return false;
    }

    public function passesSkiplist($name, $str)
    {
        if (isset($this->data->$name->regex)) {
            $regexs = explode("\n", $this->data->$name->regex);
            foreach ($regexs as $regex) {
                if (preg_match($regex, $str)) {
                    return "Failed on skiplist '$name' with regex: $regex";
                }
            }
        } else {
            $skiplist = $this->data->$name->items;
            foreach ($skiplist as $item) {
                if (fnmatch($item, $str, FNM_CASEFOLD)) {
                    return "Failed on skiplist '$name' for item: $item";
                }
            }
        }


        return true;
    }
}
