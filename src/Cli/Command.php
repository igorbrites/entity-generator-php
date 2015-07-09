<?php

namespace EntityGenerator\Cli;

use EntityGenerator\Generator\Start;

class Command
{
    public static function run()
    {
        $start = new Start();
        $start->createEntities();
    }
}