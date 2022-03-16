<?php
namespace InterNations\Component\Solr\Tests\Expression;

use CallbackFilterIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionMethod;
use SebastianBergmann\FileIterator\Facade;
use Symfony\Component\Finder\Finder;
use function array_walk;

class AnnotationsTest extends TestCase
{
    public static function getClasses(): array
    {
        array_map(
            static function (string $path) {
                include_once $path;
            },
            (new Facade())->getFilesAsArray(__DIR__ . '/../../../../src', '.php')
        );

        return array_map(
            static function (string $class) {
                return [$class];
            },
            array_filter(
                get_declared_classes(),
                static function (string $class) {
                    return strpos($class, 'InterNations\\Component\\Solr\\') === 0
                        && strpos($class, 'InterNations\\Component\\Solr\\Tests\\') === false;
                }
            )
        );
    }

    /** @dataProvider getClasses */
    public function testOptOutOfNamedArgumentSupportIsInPlace(string $className): void
    {
        $class = new ReflectionClass($className);
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (count($method->getParameters()) > 0 && $method->getDeclaringClass()->getName() === $className) {
                self::assertStringContainsString(
                    '@no-named-arguments',
                    $method->getDocComment(),
                    sprintf(
                        'Expected "%s::%s()" to have annotation @no-named-arguments',
                        $method->getDeclaringClass()->getName(),
                        $method->getName()
                    )
                );
            }
        }
        $this->addToAssertionCount(1);
    }
}
