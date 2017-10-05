<?php
namespace InterNations\Component\Solr\Tests\Expression\Exception;

use InterNations\Component\Solr\Expression\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class InvalidArgumentExceptionTest extends TestCase
{

    /** @dataProvider provideExpectationsForMessages */
    public  function testInvalidArgumentMessages(array $expectations, string $message)
    {
        $position = 2;
        $name = 'variable';
        $actual = [];

        $expectedMessage = sprintf(
            'Invalid argument #%d $%s given: expected %s, got %s',
            $position,
            $name,
            $message,
            gettype($actual)
        );

        $exception = InvalidArgumentException::invalidArgument($position, $name, $expectations, $actual);

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public static function provideExpectationsForMessages()
    {
        return [
            'Single expectation' => [['string'], 'string'],
            'Two expectations' => [['string', 'integer'], 'string or integer'],
            'More expectations' => [['string', 'boolean', 'integer', 'object'], 'string, boolean, integer or object'],
        ];
    }
}
