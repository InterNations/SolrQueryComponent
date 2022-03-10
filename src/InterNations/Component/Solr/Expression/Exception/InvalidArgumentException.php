<?php
namespace InterNations\Component\Solr\Expression\Exception;

use InvalidArgumentException as BaseInvalidArgumentException;

class InvalidArgumentException extends BaseInvalidArgumentException implements ExceptionInterface
{
    /**
     * @param string|array $expectation
     * @param mixed $actual
	 * @no-named-arguments
     */
    public static function invalidArgument(int $position, string $name, $expectation, $actual): self
    {
        $expectations = (array) $expectation;

        return new self(
            sprintf(
                'Invalid argument #%d $%s given: expected %s, got %s',
                $position,
                $name,
                self::formatExpectations($expectations),
                self::getType($actual)
            )
        );
    }

    /**
	 * @param string[] $expectations
	 * @no-named-arguments
	 */
    private static function formatExpectations(array $expectations): string
    {
        $last = array_pop($expectations);

        if (!$expectations) {
            return $last;
        }

        return implode(', ', $expectations) . ' or ' . $last;
    }

    /**
	 * @param mixed $actual
	 * @no-named-arguments
	 */
    private static function getType($actual): string
    {
        return is_object($actual) ? get_class($actual) : gettype($actual);
    }
}
