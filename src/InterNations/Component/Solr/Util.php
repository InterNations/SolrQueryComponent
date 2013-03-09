<?php
namespace InterNations\Component\Solr;

use InterNations\Component\Solr\Expression\Expression;

final class Util
{
    /**
     * @var array
     */
    private static $charMap = [
        '\\' => '\\\\',
        '+'  => '\+',
        '-'  =>'\-',
        '&'  => '\&',
        '|'  => '\|',
        '!'  => '\!',
        '('  => '\(',
        ')'  => '\)',
        '{'  => '\{',
        '}'  => '\}',
        '['  => '\[',
        ']'  => '\]',
        '^'  => '\^',
        '"'  => '\"',
        '~'  => '\~',
        '*'  => '\*',
        '?'  => '\?',
        ':'  => '\:',
        '/'  => '\/',
    ];

    /**
     * Quote a given string
     *
     * @param mixed $value
     * @return string|Expression
     */
    public static function quote($value)
    {
        if ($value instanceof Expression) {
            return $value;
        }

        if (strlen($value) > 2 && substr($value, 0, 1) === '"' && substr($value, -1, 1) === '"') {
            return $value;
        }

        return '"' . strtr($value, static::$charMap) . '"';
    }

    /**
     * Sanitizes a string
     *
     * Puts quotes around a string, treats everything else as a term
     *
     * @param mixed $value
     * @return string|Expression
     */
    public static function sanitize($value)
    {
        $type = gettype($value);

        if ($type === 'integer') {
            return (string) $value;

        } elseif ($type === 'string') {
            if ($value !== '') {
                return '"' . strtr($value, static::$charMap) . '"';
            } else {
                return $value;
            }

        } elseif ($type === 'double') {
            static $precision;
            if (!$precision) {
                $precision = ini_get('precision');
            }
            return number_format($value, $precision, '.', '');

        } elseif ($value instanceof Expression) {
            return $value;

        } elseif (empty($value)) {
            return '';
        }
    }

    /**
     * Escape a string to be safe for Solr queries
     *
     * @param mixed $value
     * @return string|Expression
     */
    public static function escape($value)
    {
        if ($value instanceof Expression) {
            return $value;
        }

        return strtr($value, static::$charMap);
    }
}
