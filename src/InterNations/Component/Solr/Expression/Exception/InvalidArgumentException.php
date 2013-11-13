<?php
namespace InterNations\Component\Solr\Expression\Exception;

use InvalidArgumentException as BaseInvalidArgumentException;

class InvalidArgumentException extends BaseInvalidArgumentException implements ExceptionInterface
{
    public static function invalidArgument($position, $name, $expectation, $actual)
    {
        return new static(
            sprintf(
                'Invalid argument #%d $%s given: expected %s, got %s',
                $position,
                $name,
                static::formatExpectation((array) $expectation),
                static::getType($actual)
            )
        );
    }

    private static function formatExpectation(array $expectation)
    {
        $last = array_pop($expectation);

        return implode($expectation, ', ') . ' or ' . $last;
    }

    private static function getType($actual)
    {
        return is_object($actual) ? get_class($actual) : gettype($actual);
    }
}
