<?php

namespace EntityGenerator\Util;

class String
{
    public static function convertToCamelCase($string, $firstLetterLowerCase = false)
    {
        $words = array_map(function($word) {
            return ucfirst(strtolower($word));
        }, explode('_', $string));

        if ($firstLetterLowerCase) {
            $words[0] = strtolower($words[0]);
        }

        return implode('', $words);
    }
}