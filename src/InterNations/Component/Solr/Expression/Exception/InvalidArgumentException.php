<?php
namespace InterNations\Component\Solr\Expression\Exception;

use InvalidArgumentException as BaseInvalidArgumentException;

class InvalidArgumentException extends BaseInvalidArgumentException implements ExceptionInterface
{
    /**
     * @param string|array $expectation
     * @param mixed $actual
     */
    public static function invalidArgument(int $position, string $name, $expectation, $actual): self
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

    /** @param string[] $expectation */
    private static function formatExpectation(array $expectation): string
    {
        $last = array_pop($expectation);

        if (!$expectation) {
            return $last;
        }

        return implode($expectation, ', ') . ' or ' . $last;
    }

    /** @param mixed $actual */
    private static function getType($actual): string
    {
        return is_object($actual) ? get_class($actual) : gettype($actual);
    }
}
