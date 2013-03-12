<?php
namespace InterNations\Component\Solr\Tests\Expression;

use InterNations\Component\Solr\Expression\ExpressionBuilder;
use InterNations\Component\Solr\Expression\GroupExpression;
use InterNations\Component\Testing\AbstractTestCase;

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
        $g = $this->eb->grp([$this->eb->phrase('bar'), $this->eb->phrase('foo'), $this->eb->phrase('baz')]);
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\GroupExpression', $g);
        $this->assertSame('("bar" "foo" "baz")', (string) $g);
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
        $date = new \DateTime('2010-10-11', new \DateTimeZone('UTC'));
        $dayRange = $this->eb->day($date);
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\RangeExpression', $dayRange);
        $this->assertSame('[2010-10-11T00:00:00Z TO 2010-10-11T23:59:59Z]', (string) $dayRange);

        $dayRange = $this->eb->field('dateField', $this->eb->day($date));
        $this->assertInstanceOf('InterNations\Component\Solr\Expression\FieldExpression', $dayRange);
        $this->assertSame('dateField:[2010-10-11T00:00:00Z TO 2010-10-11T23:59:59Z]', (string) $dayRange);
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
        $this->assertSame('("foo" AND "bar")', (string) $this->eb->grp(['foo', 'bar'], GroupExpression::TYPE_AND));
        $this->assertSame('("foo" OR "bar")', (string) $this->eb->grp(['foo', 'bar'], GroupExpression::TYPE_OR));

        $this->assertSame('("foo" OR "bar")', (string) $this->eb->grp(['foo', 'bar', GroupExpression::TYPE_OR]));
        $this->assertSame('("foo" AND "bar")', (string) $this->eb->grp(['foo', 'bar', GroupExpression::TYPE_AND]));

        $this->assertSame('("foo" OR "bar")', (string) $this->eb->grp('foo', 'bar', GroupExpression::TYPE_OR));
        $this->assertSame('("foo" OR "bar")', (string) $this->eb->grp('foo', 'bar', GroupExpression::TYPE_OR));
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
            '("foo bar baz" -(+field1:"foo"^10 -field2:test~0.2 "word1 word2"~3 [from TO to] field3:{1 TO 10}))',
            (string) $q
        );
    }

    public function testDateRange()
    {
        $from = new \DateTime('2010-10-11 02:00:00', new \DateTimeZone('Europe/Berlin'));
        $to = new \DateTime('2010-10-22 01:59:59', new \DateTimeZone('Europe/Berlin'));

        $this->assertSame(
            '[2010-10-11T00:00:00Z TO 2010-10-21T23:59:59Z]',
            (string) $this->eb->dateRange($from, $to)
        );

        $this->assertSame(
            'dateField:[2010-10-11T00:00:00Z TO 2010-10-21T23:59:59Z]',
            (string) $this->eb->field('dateField', $this->eb->dateRange($from, $to))
        );

        $this->assertSame(
            '[* TO 2010-10-21T23:59:59Z]',
            (string) $this->eb->dateRange(null, $to)
        );

        $this->assertSame(
            '[2010-10-11T00:00:00Z TO *]',
            (string) $this->eb->dateRange($from, null)
        );

        $this->assertSame(
            '{2010-10-11T00:00:00Z TO 2010-10-21T23:59:59Z}',
            (string) $this->eb->dateRange($from, $to, false)
        );

        $this->assertNull(
            $this->eb->dateRange(null, null)
        );
    }

    public function testRange()
    {
        $this->assertSame('[A TO Z]', (string) $this->eb->range('A', 'Z'));
        $this->assertSame('[A TO Z]', (string) $this->eb->range('A', 'Z', true));
        $this->assertSame('{A TO Z}', (string) $this->eb->range('A', 'Z', false));
    }
}
