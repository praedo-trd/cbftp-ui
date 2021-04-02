<?php

namespace TRD\Utility;

class ConsoleDebug
{
    public static function debug($str)
    {
        echo(new \Malenki\Ansi(':: '.$str.PHP_EOL))->fg('green');
    }

    public static function incoming($str)
    {
        echo(new \Malenki\Ansi('> [' . date('H:i:s') . '] ' .$str.PHP_EOL))->fg('blue');
    }
}
