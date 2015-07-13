<?php

namespace EntityGenerator\Cli;

use EntityGenerator\Generator\Start;

class Command
{
    public static function run()
    {
        echo "Starting Entity Generator\n";

        $start = new Start();
        $start->createEntities();
    }
}