<?php

namespace TRD\Twig\Extension;

class IsBooleanExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
          new \Twig_SimpleFunction('is_boolean', array($this, 'is_boolean')),
      );
    }

    public function is_boolean($variable)
    {
        return is_bool($variable);
    }

    public function getName()
    {
        return 'is_boolean';
    }
}
