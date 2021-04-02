<?php

namespace TRD\Twig\Extension;

class GetEnvExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
          new \Twig_SimpleFunction('getenv', array($this, 'getenv')),
      );
    }

    public function getenv($key)
    {
        return isset($_ENV[$key]) ? $_ENV[$key] : false;
    }

    public function getName()
    {
        return 'getenv';
    }
}
