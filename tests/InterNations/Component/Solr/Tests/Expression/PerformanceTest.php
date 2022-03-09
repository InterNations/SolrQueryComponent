<?php
namespace InterNations\Component\Solr\Tests\Expression;

use InterNations\Component\Solr\Expression\GroupExpression;
use InterNations\Component\Testing\TimingTrait;
use PHPUnit\Framework\TestCase;

/**
 * @group performance
 */
class PerformanceTest extends TestCase
{
    use TimingTrait;

    public function setUp(): void
    {
        if (extension_loaded('xdebug')) {
            $this->markTestSkipped('xdebug extension is enabled. Performance tests skipped');
        }
    }

    public function testGroupingPerformance_Int()
    {
        $list = range(0, 10000);

        $this->assertTiming(
            20,
            function () use ($list) {
                $group = new GroupExpression($list);
                $group->__toString();
            }
        );
    }

    public function testGroupingPerformance_Double()
    {
        $list = range(0, 10000);
        foreach ($list as $k => $v) {
            $list[$k] = (float) $v;
        }

        $this->assertTiming(
            30,
            function () use ($list) {
                $group = new GroupExpression($list);
                $group->__toString();
            }
        );
    }

    public function testGroupingPerformance_String()
    {
        $list = range(0, 10000);
        foreach ($list as $k => $v) {
            $list[$k] = (string) $v;
        }

        $this->assertTiming(
            61,
            function () use ($list) {
                $group = new GroupExpression($list);
                $group->__toString();
            }
        );
    }
}
