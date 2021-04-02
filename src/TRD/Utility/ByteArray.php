<?php

namespace TRD\Utility;

class ByteArray
{
    private $bytes = [];

    public function __construct($string)
    {
        $this->bytes = $this->fromString($string);
    }
  
    public function fromString($string)
    {
        return array_values(unpack('C*', $string));
    }
    
    public function append($string)
    {
        $this->bytes = array_merge($this->bytes, $this->fromString($string));
    }

    public function toEnd($startingByte)
    {
        return array_slice($this->bytes, $startingByte);
    }

    public static function toString($bytes)
    {
        $chars = array_map("chr", $bytes);
        return join($chars);
    }

    public function asString()
    {
        return self::toString($this->bytes);
    }

    public function getBytes()
    {
        return $this->bytes;
    }

    public function findByte($byte)
    {
        return array_search(ord($byte), $this->bytes);
    }

    public function split($byte)
    {
        $index = $this->findByte($byte);
        if ($index !== false) {
            return array_slice($this->bytes, 0, $index - 1);
        }
        return [];
    }

    public function splitToString($byte)
    {
        return self::toString($this->split($byte));
    }

    private function eraseUntil($index)
    {
        $this->bytes = array_slice($this->bytes, $index + 1);
    }

    public function pickUntilByte($byte)
    {
        $index = $this->findByte($byte);
        if ($index !== false) {
            $string = self::toString(array_slice($this->bytes, 0, $index));
            $this->eraseUntil($index);
            return $string;
        }
        return false;
    }
}
