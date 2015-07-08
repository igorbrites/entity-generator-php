<?php

namespace EntityGenerator\Cli;

use EntityGenerator\Generator\Start;

class Command
{
    public static function run()
    {
        $configFile = realpath(dirname(__FILE__) . '/../../../config.json');
        $config = [];

        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
        }

        $start = new Start($config);
        $start->createEntities();
    }
}