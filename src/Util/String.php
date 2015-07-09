<?php

namespace EntityGenerator\Util;

class String
{
    /**
     * Convert a string from 'underscore_based' to 'UnderscoreBased'
     *
     * @param $string
     * @param bool|false $firstLetterLowerCase
     *
     * @return string
     */
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

    /**
     * Convert ENUM options from 'enum('option1', 'option2')' to ['option1', 'option2']
     *
     * @param $enum
     *
     * @return array
     */
    public static function convertEnumToArray($enum)
    {
        return explode(",", str_replace("'", "", substr($enum, 5, (strlen($enum) - 6))));
    }
}