<?php
namespace InterNations\Component\Solr\Tests\Expression;

use InterNations\Component\Testing\AbstractTestCase;
use InterNations\Component\Solr\Expression\GroupExpression;

class PerformanceTest extends AbstractTestCase
{
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
            30,
            function () use ($list) {
                $group = new GroupExpression($list);
                $group->__toString();
            }
        );
    }

    protected function assertTiming($maxDurationInMs, callable $callable, $runs = 20)
    {
        $duration = 0;

        for ($a = 0; $a < $runs; ++$a) {
            $start = microtime(true);
            $callable();
            $end = microtime(true);
            $duration += ($end - $start);
        }

        $this->assertLessThanOrEqual($maxDurationInMs, ($duration / $runs) * 1000);
    }
}
