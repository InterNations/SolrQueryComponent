<?php
namespace InterNations\Component\Solr;

use InterNations\Component\Solr\Expr\Expr;

final class Util
{
    /**
     * @var array
     */
    private static $charMap = [
        '\\' => '\\\\',
        '+'  => '\+',
        '-'  =>'\-',
        '&' => '\&',
        '|' => '\|',
        '!'  => '\!',
        '('  => '\(',
        ')'  => '\)',
        '{' => '\{',
        '}' => '\}',
        '[' => '\[',
        ']' => '\]',
        '^' => '\^',
        '"' => '\"',
        '~' => '\~',
        '*' => '\*',
        '?' => '\?',
        ':' => '\:',
        '/' => '\/',
    ];

    /**
     * Quote a given string
     *
     * @param string|Expr $string
     * @return string
     */
    public static function quote($string)
    {
        if ($string instanceof Expr) {
            return $string;
        }

        return '"' . static::escape($string) . '"';
    }

    /**
     * Sanitizes a string
     *
     * Puts quotes around a multi-part string, treats everything else as a term
     *
     * @param $string
     * @return int|Expr|string
     */
    public static function sanitize($string)
    {
        if ($string instanceof Expr) {
            return $string;
        }


        if (is_float($string)) {
            return number_format($string, ini_get('precision'), '.', '');
        }

        if (is_int($string)) {
            return (string) $string;
        }

        if (!preg_match('/\s/', $string)) {
            return static::escape($string);
        }

        return static::quote($string);
    }

    /**
     * Escape a string to be safe for solr queries
     *
     * @param string|Expr $string
     * @return Expr|string
     */
    public static function escape($string)
    {
        if ($string instanceof Expr) {
            return $string;
        }

        return strtr($string, static::$charMap);
    }
}
