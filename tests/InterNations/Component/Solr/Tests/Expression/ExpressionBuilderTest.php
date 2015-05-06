<?php
namespace InterNations\Component\Solr\Tests\Expression;

use InterNations\Component\Solr\Expression\CompositeExpression;
use InterNations\Component\Solr\Expression\ExpressionBuilder;
use InterNations\Component\Solr\Expression\GroupExpression;
use InterNations\Component\Solr\Expression\ParameterExpression;
use InterNations\Component\Testing\AbstractTestCase;
use DateTime;
use DateTimeZone;

class ExpressionBuilderTest extends AbstractTestCase
{
    /**
     * @var ExpressionBuilder
     */
    private $eb;

    public function setUp()
    {
        $this->eb = new ExpressionBuilder();
    }

    public function testEqWithPhrase()
    {
        $eq = $this->eb->eq($this->eb->phrase('test foo'));
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\PhraseExpression', $eq);
        $this->assertSame('"test foo"', (string) $eq);
    }

    public function testEqWithTerm()
    {
        $eq = $this->eb->eq('foo:bar');
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\PhraseExpression', $eq);
        $this->assertSame('"foo\:bar"', (string) $eq);
    }

    public function testEqWithField()
    {
        $eq = $this->eb->eq('test');
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\PhraseExpression', $eq);

        $eq = $this->eb->field('field', $eq);
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\FieldExpression', $eq);
        $this->assertSame('field:"test"', (string) $eq);
    }

    public function testPhrase()
    {
        $p = $this->eb->phrase('foo bar');
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\PhraseExpression', $p);
        $this->assertSame('"foo bar"', (string) $p);

        $p = $this->eb->field('field', $p);
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\FieldExpression', $p);
        $this->assertSame('field:"foo bar"', (string) $p);
    }

    public function testBoost()
    {
        $b = $this->eb->boost($this->eb->phrase('foo bar'), 10);
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\BoostExpression', $b);
        $this->assertSame('"foo bar"^10', (string) $b);

        $b = $this->eb->field('field', $this->eb->boost($this->eb->phrase('foo bar'), 10));
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\FieldExpression', $b);
        $this->assertSame('field:"foo bar"^10', (string) $b);
    }

    public function testWildcard()
    {
        $w = $this->eb->wild($this->eb->phrase('foo bar'), '?', 'sfx');
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\WildcardExpression', $w);
        $this->assertSame('"foo bar?sfx"', (string) $w);

        $w = $this->eb->field('field', $this->eb->wild($this->eb->phrase('foo bar'), '?'));
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\FieldExpression', $w);
        $this->assertSame('field:"foo bar?"', (string) $w);

        $w = $this->eb->wild('foo', '*', $this->eb->wild('bar', '?'));
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\WildcardExpression', $w);
        $this->assertSame('foo*bar?', (string) $w);

        $w = $this->eb->wild($this->eb->wild('', '*', 'foo'), '*', $this->eb->wild('bar', '?'));
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\WildcardExpression', $w);
        $this->assertSame('*foo*bar?', (string) $w);
    }

    public function testProhibitedExpr()
    {
        $n = $this->eb->prhb('foo');
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\BooleanExpression', $n);
        $this->assertSame('-foo', (string) $n);

        $n = $this->eb->prhb($this->eb->field('field', 'foo'));
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\BooleanExpression', $n);
        $this->assertSame('-field:"foo"', (string) $n);
    }

    public function testRequiredExpr()
    {
        $n = $this->eb->req('foo');
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\BooleanExpression', $n);
        $this->assertSame('+foo', (string) $n);

        $n = $this->eb->req($this->eb->field('field', 'foo'));
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\BooleanExpression', $n);
        $this->assertSame('+field:"foo"', (string) $n);
    }

    public function testGrouping()
    {
        $g = $this->eb->grp($this->eb->phrase('foo bar'));
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\GroupExpression', $g);
        $this->assertSame('("foo bar")', (string) $g);
    }

    public function testGroupingMultipleParams()
    {
        $g = $this->eb->grp($this->eb->phrase('foo'), $this->eb->phrase('bar'), $this->eb->phrase('baz'));
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\GroupExpression', $g);
        $this->assertSame('("foo" "bar" "baz")', (string) $g);
    }

    public function testGroupingSingleParamAsArray()
    {
        $g = $this->eb->grp(array($this->eb->phrase('bar'), $this->eb->phrase('foo'), $this->eb->phrase('baz')));
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\GroupExpression', $g);
        $this->assertSame('("bar" "foo" "baz")', (string) $g);
    }

    public function testGroupingWithAndX()
    {
        $g = $this->eb->andX($this->eb->phrase('foo'), $this->eb->phrase('bar'), $this->eb->phrase('baz'));
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\GroupExpression', $g);
        $this->assertSame('("foo" AND "bar" AND "baz")', (string) $g);
    }

    public function testGroupingWithAndXAndEmptyExpressions()
    {
        $g = $this->eb->andX($this->eb->phrase('foo'), $this->eb->andX(null, null, ''), $this->eb->phrase('bar'));
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\GroupExpression', $g);
        $this->assertSame('("foo" AND "bar")', (string) $g);
    }

    public function testGroupingWithOrX()
    {
        $g = $this->eb->orX($this->eb->phrase('foo'), $this->eb->phrase('bar'), $this->eb->phrase('baz'));
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\GroupExpression', $g);
        $this->assertSame('("foo" OR "bar" OR "baz")', (string) $g);
    }

    public function testGroupingWithOrXAndEmptyExpressions()
    {
        $g = $this->eb->orX($this->eb->phrase('foo'), $this->eb->orX(null, null, ''), $this->eb->phrase('bar'));
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\GroupExpression', $g);
        $this->assertSame('("foo" OR "bar")', (string) $g);
    }

    public function testField()
    {
        $f = $this->eb->field('field', 'query');
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\FieldExpression', $f);
        $this->assertSame('field:"query"', (string) $f);
    }

    public function testFuzzy()
    {
        $f = $this->eb->fzz('test');
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\FuzzyExpression', $f);
        $this->assertSame('test~', (string) $f);

        $f = $this->eb->fzz('test', 0.2);
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\FuzzyExpression', $f);
        $this->assertSame('test~0.2', (string) $f);

        $f = $this->eb->fzz($this->eb->field('field', 'test'), 0.2);
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\FuzzyExpression', $f);
        $this->assertSame('field:"test"~0.2', (string) $f);
    }

    public function testProximityQuery()
    {
        $p = $this->eb->prx('word1', 'word2', 10);
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\ProximityExpression', $p);
        $this->assertSame('"word1 word2"~10', (string) $p);

        $p = $this->eb->field('field', $this->eb->prx('word1', 'word2', 10));
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\FieldExpression', $p);
        $this->assertSame('field:"word1 word2"~10', (string) $p);

        $p = $this->eb->prx('word1 word2 word3', 10);
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\ProximityExpression', $p);
        $this->assertSame('"word1 word2 word3"~10', (string) $p);

        $p = $this->eb->prx('word1', 'word2', 'word3', 10);
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\ProximityExpression', $p);
        $this->assertSame('"word1 word2 word3"~10', (string) $p);

        $p = $this->eb->prx(array('word1', 'word2', 'word3'), 10);
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\ProximityExpression', $p);
        $this->assertSame('"word1 word2 word3"~10', (string) $p);

        $p = $this->eb->prx(array(), array('word1', 'word2'), 'word3', 10);
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\ProximityExpression', $p);
        $this->assertSame('"word1 word2 word3"~10', (string) $p);

        $this->assertNull($this->eb->prx(array(), 10));
        $this->assertNull($this->eb->prx(10));
    }

    public function testLiteralStr()
    {
        $expr = $this->eb->lit('foo:bar');
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\Expression', $expr);
        $this->assertSame('foo:bar', (string) $expr);
        $this->assertSame('0', (string) $this->eb->lit(0));
    }

    public function testBool()
    {
        $expr = $this->eb->bool('foo', true);
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\BooleanExpression', $expr);
        $this->assertSame('+foo', (string) $expr);

        $expr = $this->eb->bool($this->eb->field('field', 'foo'), true);
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\BooleanExpression', $expr);
        $this->assertSame('+field:"foo"', (string) $expr);

        $expr = $this->eb->bool('foo', false);
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\BooleanExpression', $expr);
        $this->assertSame('-foo', (string) $expr);

        $expr = $this->eb->bool($this->eb->field('field', 'foo'), false);
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\BooleanExpression', $expr);
        $this->assertSame('-field:"foo"', (string) $expr);

        $expr = $this->eb->bool('foo', null);
        $this->assertSame('foo', $expr);

        $expr = $this->eb->bool('foo', 1);
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\BooleanExpression', $expr);
        $this->assertSame('+foo', (string) $expr);

        $expr = $this->eb->bool('foo', 0);
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\BooleanExpression', $expr);
        $this->assertSame('-foo', (string) $expr);
    }

    public function testDayBuilder()
    {
        $date = new DateTime('2010-10-11', new DateTimeZone('UTC'));
        $dayRange = $this->eb->day($date);
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\RangeExpression', $dayRange);
        $this->assertSame('[2010-10-11T00:00:00Z TO 2010-10-11T23:59:59Z]', (string) $dayRange);

        $dayRange = $this->eb->field('dateField', $this->eb->day($date));
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\FieldExpression', $dayRange);
        $this->assertSame('dateField:[2010-10-11T00:00:00Z TO 2010-10-11T23:59:59Z]', (string) $dayRange);
    }

    public static function getStartOfDayData()
    {
        return array(
            array('2010-10-10T00:00:00Z', '2010-10-11 00:00:00', 'Europe/Berlin'),
            array('2010-10-11T00:00:00Z', '2010-10-11 22:00:00', 'Europe/Moscow'),
            array('2010-10-11T00:00:00Z', '2010-10-11 01:00:00', 'Europe/Moscow', 'Europe/Moscow'),
            array('2010-10-10T00:00:00Z', '2010-10-11 01:00:00', 'Europe/Moscow', 'Europe/Berlin'),
            array('2010-10-11T00:00:00Z', '2010-10-10 22:00:00', 'Europe/Berlin', 'Europe/Moscow'),
            array(null, null, null),
        );
    }

    /** @dataProvider getStartOfDayData */
    public function testBeginningOfDayBuilder($expected, $date, $timezone, $defaultTimezone = null)
    {
        if ($defaultTimezone) {
            $this->eb->setDefaultTimezone($defaultTimezone);
        }
        if ($date !== null) {
            $date = new DateTime($date, new DateTimeZone($timezone));
        }
        $result = $this->eb->startOfDay($date);
        $this->assertSame($expected, $result ? (string) $result : null);
    }


    public static function getEndOfDayData()
    {
        return array(
            array('2010-10-10T23:59:59Z', '2010-10-11 00:00:00', 'Europe/Berlin'),
            array('2010-10-11T23:59:59Z', '2010-10-11 22:00:00', 'Europe/Moscow'),
            array('2010-10-11T23:59:59Z', '2010-10-11 01:00:00', 'Europe/Moscow', 'Europe/Moscow'),
            array('2010-10-10T23:59:59Z', '2010-10-11 01:00:00', 'Europe/Moscow', 'Europe/Berlin'),
            array('2010-10-11T23:59:59Z', '2010-10-10 22:00:00', 'Europe/Berlin', 'Europe/Moscow'),
            array(null, null, null),
        );
    }

    /** @dataProvider getEndOfDayData */
    public function testEndOfDayBuilder($expected, $date, $timezone, $defaultTimezone = null)
    {
        if ($defaultTimezone) {
            $this->eb->setDefaultTimezone($defaultTimezone);
        }
        if ($date) {
            $date = new DateTime($date, new DateTimeZone($timezone));
        }
        $result = $this->eb->endOfDay($date);
        $this->assertSame($expected, $result ? (string) $result : null);
    }

    public function testDefaultQueryForAllIfNullGiven()
    {
        $this->assertSame('*:*', (string) $this->eb->all(''));
        $this->assertSame('*:*', (string) $this->eb->all(null));
        $this->assertSame('0', (string) $this->eb->all(0));
        $this->assertSame('0', (string) $this->eb->all('0'));
    }

    public function testFallthroughIfNullGiven()
    {
        $this->assertNull($this->eb->field('field', null));
        $this->assertNotNull($this->eb->field('field', 0));
        $this->assertNotNull($this->eb->field('field', '0'));
        $this->assertNull($this->eb->boost('expr', null));
        $this->assertNull($this->eb->boost(null, 1));
        $this->assertNull($this->eb->boost(null, null));
        $this->assertNotNull($this->eb->boost(0, 1));
        $this->assertNotNull($this->eb->boost('0', 1));
        $this->assertNull($this->eb->eq(null, 'field'));
        $this->assertNotNull($this->eb->eq(0, 'field'));
        $this->assertNotNull($this->eb->eq('0', 'field'));
        $this->assertNull($this->eb->wild(null));
        $this->assertNotNull($this->eb->wild(0));
        $this->assertNotNull($this->eb->wild('0'));
        $this->assertNull($this->eb->wild('pref', null));
        $this->assertNull($this->eb->wild(null, null));
        $this->assertNull($this->eb->req(null));
        $this->assertNotNull($this->eb->req(0));
        $this->assertNotNull($this->eb->req('0'));
        $this->assertNull($this->eb->prhb(null));
        $this->assertNotNull($this->eb->prhb(0));
        $this->assertNotNull($this->eb->prhb('0'));
        $this->assertNull($this->eb->phrase(null));
        $this->assertNotNull($this->eb->phrase(0));
        $this->assertNotNull($this->eb->phrase('0'));
        $this->assertNull($this->eb->phrase(null, 'field'));
        $this->assertNotNull($this->eb->phrase(0, 'field'));
        $this->assertNotNull($this->eb->phrase('0', 'field'));
        $this->assertNull($this->eb->grp(null, null, false));
        $this->assertNotNull($this->eb->grp(0, null, false));
        $this->assertNull($this->eb->grp());
        $this->assertNull($this->eb->day());
        $this->assertNull($this->eb->day(''));
    }

    public function testGroupingTypes()
    {
        $this->assertSame('("foo" "bar")', (string) $this->eb->grp(array('foo', 'bar')));
        $this->assertSame('("foo" AND "bar")', (string) $this->eb->grp(array('foo', 'bar'), GroupExpression::TYPE_AND));
        $this->assertSame('("foo" OR "bar")', (string) $this->eb->grp(array('foo', 'bar'), GroupExpression::TYPE_OR));

        $this->assertSame('("foo" OR "bar")', (string) $this->eb->grp(array('foo', 'bar', GroupExpression::TYPE_OR)));
        $this->assertSame('("foo" AND "bar")', (string) $this->eb->grp(array('foo', 'bar', GroupExpression::TYPE_AND)));

        $this->assertSame('("foo" OR "bar")', (string) $this->eb->grp('foo', 'bar', GroupExpression::TYPE_OR));
        $this->assertSame('("foo" OR "bar")', (string) $this->eb->grp('foo', 'bar', GroupExpression::TYPE_OR));
    }

    public function testCompositingTypes()
    {
        $this->assertSame('"foo" "bar"', (string) $this->eb->comp(array('foo', 'bar')));
        $this->assertSame('"foo" AND "bar"', (string) $this->eb->comp(array('foo', 'bar'), CompositeExpression::TYPE_AND));
        $this->assertSame('"foo" OR "bar"', (string) $this->eb->comp(array('foo', 'bar'), CompositeExpression::TYPE_OR));

        $this->assertSame('"foo" OR "bar"', (string) $this->eb->comp(array('foo', 'bar', CompositeExpression::TYPE_OR)));
        $this->assertSame('"foo" AND "bar"', (string) $this->eb->comp(array('foo', 'bar', CompositeExpression::TYPE_AND)));

        $this->assertSame('"foo" OR "bar"', (string) $this->eb->comp('foo', 'bar', CompositeExpression::TYPE_OR));
        $this->assertSame('"foo" OR "bar"', (string) $this->eb->comp('foo', 'bar', CompositeExpression::TYPE_OR));
    }

    public function testRealisticQueryExamples()
    {
        $qb = $this->eb;

        $q = $qb->grp(
                $qb->req($qb->field('test', $qb->wild('foo', '*'))),
                $qb->prhb('bar')
             );
        $this->assertSame('(+test:foo* -bar)', (string) $q);

        $q = $qb->grp(
                $qb->req($qb->field('test', $qb->wild('foo', '*'))),
                $qb->prhb('bar')
             );
        $this->assertSame('(+test:foo* -bar)', (string) $q);

        $q = $qb->grp(
                $qb->phrase('foo bar baz'),
                $qb->prhb(
                    $qb->grp(
                        $qb->req($qb->field('field1', $qb->boost('foo', 10))),
                        $qb->prhb($qb->field('field2', $qb->fzz('test', 0.2))),
                        $qb->prx('word1', 'word2', 3),
                        $qb->range('from', 'to'),
                        $qb->field('field3', $qb->btwnRange('1', 10))
                    )
                )
             );
        $this->assertSame(
            '("foo bar baz" -(+field1:"foo"^10 -field2:test~0.2 "word1 word2"~3 ["from" TO "to"] field3:{"1" TO 10}))',
            (string) $q
        );
    }

    public static function getDateRangeData()
    {
        return array(
            array(
                '[2010-10-11T00:00:00Z TO 2010-10-21T23:59:59Z]',
                '2010-10-11 02:00:00',
                'Europe/Berlin',
                '2010-10-22 01:59:59',
                'Europe/Berlin'
            ),
            array(
                '[2010-10-11T00:00:00Z TO 2010-10-21T23:59:59Z]',
                '2010-10-11 02:00:00',
                'Europe/Berlin',
                '2010-10-22 01:59:59',
                'Europe/Berlin',
                true
            ),
            array(
                '[2010-10-11T00:00:00Z TO 2010-10-21T23:59:59Z]',
                '2010-10-11 02:00:00',
                'Europe/Berlin',
                '2010-10-22 01:59:59',
                'Europe/Berlin'
            ),
            array(
                '[2010-10-11T00:00:00Z TO 2010-10-21T23:59:59Z]',
                '2010-10-11 02:00:00',
                'Europe/Berlin',
                '2010-10-22 01:59:59',
                'Europe/Berlin',
                true
            ),
            array(
                '[* TO 2010-10-21T23:59:59Z]',
                null,
                null,
                '2010-10-22 01:59:59',
                'Europe/Berlin'
            ),
            array(
                '[2010-10-11T00:00:00Z TO *]',
                '2010-10-11 02:00:00',
                'Europe/Berlin',
                null,
                null
            ),
            array(
                '{2010-10-11T00:00:00Z TO 2010-10-21T23:59:59Z}',
                '2010-10-11 02:00:00',
                'Europe/Berlin',
                '2010-10-22 01:59:59',
                'Europe/Berlin',
                false
            ),
            array(
                null,
                null,
                null,
                null,
                null
            ),
            array(
                '[2010-10-11T04:00:00Z TO 2010-10-22T03:59:59Z]',
                '2010-10-11 02:00:00',
                'Europe/Berlin',
                '2010-10-22 01:59:59',
                'Europe/Berlin',
                null,
                'null',
                'Europe/Moscow',
            ),
            array(
                '[2010-10-11T04:00:00Z TO 2010-10-22T03:59:59Z]',
                '2010-10-11 02:00:00',
                'Europe/Berlin',
                '2010-10-22 01:59:59',
                'Europe/Berlin',
                true,
                'null',
                'Europe/Moscow',
            ),
            array(
                '[2010-10-11T04:00:00Z TO 2010-10-22T03:59:59Z]',
                '2010-10-11 02:00:00',
                'Europe/Berlin',
                '2010-10-22 01:59:59',
                'Europe/Berlin',
                null,
                'null',
                'Europe/Moscow',
            ),
            array(
                '[2010-10-11T04:00:00Z TO 2010-10-22T03:59:59Z]',
                '2010-10-11 02:00:00',
                'Europe/Berlin',
                '2010-10-22 01:59:59',
                'Europe/Berlin',
                true,
                'null',
                'Europe/Moscow',
            ),
            array(
                '[* TO 2010-10-22T03:59:59Z]',
                null,
                null,
                '2010-10-22 01:59:59',
                'Europe/Berlin',
                null,
                'null',
                'Europe/Moscow'
            ),
            array(
                '[2010-10-11T04:00:00Z TO *]',
                '2010-10-11 02:00:00',
                'Europe/Berlin',
                null,
                null,
                null,
                'null',
                'Europe/Moscow',
            ),
            array(
                '{2010-10-11T04:00:00Z TO 2010-10-22T03:59:59Z}',
                '2010-10-11 02:00:00',
                'Europe/Berlin',
                '2010-10-22 01:59:59',
                'Europe/Berlin',
                false,
                'null',
                'Europe/Moscow',
            ),
            array(
                '{2010-10-11T02:00:00Z TO 2010-10-22T01:59:59Z}',
                '2010-10-11 02:00:00',
                'Europe/Berlin',
                '2010-10-22 01:59:59',
                'Europe/Berlin',
                false,
                'Europe/Berlin',
                'Europe/Moscow',
            ),
        );
    }

    /** @dataProvider getDateRangeData */
    public function testDateRange(
        $expected,
        $from,
        $fromTimezone,
        $to,
        $toTimezone,
        $inclusive = null,
        $solrTimezone = 'null',
        $defaultTimezone = null
    )
    {
        if ($defaultTimezone) {
            $this->eb->setDefaultTimezone($defaultTimezone);
        }
        if ($from) {
            $from = new DateTime($from, new DateTimeZone($fromTimezone));
        }
        if ($to) {
            $to = new DateTime($to, new DateTimeZone($toTimezone));
        }

        $arguments = array($from, $to);
        if ($inclusive !== null) {
            $arguments[] = $inclusive;

            if ($solrTimezone !== 'null') {
                $arguments[] = $solrTimezone;
            }
        }

        $result = call_user_func_array(array($this->eb, 'dateRange'), $arguments);
        $this->assertSame($expected, $result !== null ? (string) $result : $result);
    }

    public function testRange()
    {
        $this->assertSame('["A" TO "Z"]', (string) $this->eb->range('A', 'Z'));
        $this->assertSame('["A" TO "Z"]', (string) $this->eb->range('A', 'Z', true));
        $this->assertSame('{"A" TO "Z"}', (string) $this->eb->range('A', 'Z', false));
    }

    public function testFunc()
    {
        $this->assertSame('func()', (string) $this->eb->func('func'));
        $this->assertSame('func()', (string) $this->eb->func('func', null));
        $this->assertSame('func("foo", "bar")', (string) $this->eb->func('func', array('foo', 'bar')));
        $this->assertSame('func("foo")', (string) $this->eb->func('func', $this->eb->params(array('foo'))));
        $this->assertSame('func("foo", "bar")', (string) $this->eb->func('func', $this->eb->params(array('foo', 'bar'))));
        $this->assertSame('func("foo", "bar")', (string) $this->eb->func('func', $this->eb->params('foo', 'bar')));
        $this->assertSame('func("foo")', (string) $this->eb->func('func', $this->eb->params('foo')));
        $this->assertSame('func()', (string) $this->eb->func('func', $this->eb->params()));
        $this->assertSame('func("", "")', (string) $this->eb->func('func', $this->eb->params(null, null)));
    }

    public function testLocalParams()
    {
        $this->assertSame('{!dismax}', (string) $this->eb->localParams('dismax'));
        $this->assertSame('{!dismax} "My Query"', (string) $this->eb->localParams('dismax', 'My Query'));
        $this->assertSame(
            '{!dismax qf="field"} "My Query"',
            (string) $this->eb->localParams('dismax', array('qf' => 'field'), 'My Query')
        );
        $this->assertSame('{!dismax qf="field"}', (string) $this->eb->localParams('dismax', array('qf' => 'field')));
        $this->assertSame('{!type=dismax qf="field"}', (string) $this->eb->localParams('dismax', array('qf' => 'field'), false));
        $this->assertSame('{!func}field', (string) $this->eb->field('field', $this->eb->localParams('func')));
    }

    public function testGeofilt()
    {
        $this->assertSame('{!geofilt sfield="geofield"}', (string) $this->eb->geofilt('geofield'));
        $this->assertSame(
            '{!geofilt sfield="geofield" pt="1.234500000000,6.789000000000"}',
            (string) $this->eb->geofilt('geofield', $this->eb->latLong(1.2345, 6.789))
        );
        $this->assertSame(
            '{!geofilt sfield="geofield" pt="1.234500000000,6.789000000000" d=100}',
            (string) $this->eb->geofilt('geofield', $this->eb->latLong(1.2345, 6.789), 100)
        );
        $this->assertSame(
            '{!geofilt sfield="geofield" score="miles"}',
            (string) $this->eb->geofilt('geofield', null, null, array('score' => 'miles'))
        );
        $this->assertSame(
            '{!geofilt sfield="geofield" pt="1.234500000000,6.789000000000" d=999}',
            (string) $this->eb->geofilt('geofield', $this->eb->latLong(1.2345, 6.789), 100, array('d' => 999))
        );
    }

    public function testLatLong()
    {
        $this->assertSame('60.166667000000,24.933333000000', (string) $this->eb->latlong(60.166667, 24.933333));
        $this->assertSame('-33.799508000000,151.284072000000', (string) $this->eb->latlong(-33.799508, 151.284072));

        $this->assertSame('37.7707,-119.5120', (string) $this->eb->latlong(37.770715, -119.512024, 4));
    }

    public static function getDateTimeData()
    {
        return array(
            array('2012-12-13T14:15:16Z', '2012-12-13 15:15:16', 'Europe/Berlin', 'null'),
            array('2012-12-13T14:15:16Z', '2012-12-13 15:15:16', 'Europe/Berlin', 'UTC'),
            array('2012-12-13T14:15:16Z', '2012-12-13 11:15:16', 'Europe/Berlin', 'Europe/Moscow'),
            array('2012-12-13T14:15:16Z', '2012-12-13 14:15:16', 'Europe/Berlin', null),
            array('2012-12-13T14:15:16Z', '2012-12-13 11:15:16', 'Europe/Berlin', 'Europe/Moscow'),
            array('2012-12-13T14:15:16Z', '2012-12-13 11:15:16', 'Europe/Berlin', 'null', 'Europe/Moscow'),
            array('2012-12-13T14:15:16Z', '2012-12-13 14:15:16', 'Europe/Berlin', null, 'Europe/Moscow'),
            array('2012-12-13T11:15:16Z', '2012-12-13 11:15:16', 'Europe/Berlin', 'Europe/Berlin', 'Europe/Moscow'),
            array('2012-12-13T14:15:16Z', '2012-12-13 11:15:16', 'Europe/Berlin', 'null', 'Europe/Moscow'),
            array('*', null, null),
        );
    }

    /** @dataProvider getDateTimeData */
    public function testDateExpressions(
        $expected,
        $date,
        $dateTimezone = null,
        $solrTimezone = null,
        $defaultTimezone = null
    )
    {
        if ($defaultTimezone) {
            $this->eb->setDefaultTimezone($defaultTimezone);
        }
        if ($date !== null) {
            $date = new DateTime($date, new DateTimeZone($dateTimezone));
        }

        if ($solrTimezone === 'null') {
            $this->assertSame($expected, (string) $this->eb->date($date));
        } else {
            $this->assertSame($expected, (string) $this->eb->date($date, $solrTimezone));
        }
    }

    public function testNoCache()
    {
        $this->assertSame('{!cache=false}', (string) $this->eb->noCache('*:*'));
        $this->assertSame('', (string) $this->eb->noCache(''));
    }

    public function testSetInvalidDefaultTimezone()
    {
        $this->setExpectedException(
            'InterNations\Component\Solr\Expression\Exception\InvalidArgumentException',
            'Invalid argument #1 $timezone given: expected string or DateTimeZone, got bool'
        );
        $this->eb->setDefaultTimezone(true);
    }
}
