<?php
namespace InterNations\Component\Solr\Tests\Expr;

use InterNations\Component\Testing\AbstractTestCase;
use InterNations\Component\Solr\Expr\Expr;
use InterNations\Component\Solr\Expr\DateTimeExpr;
use InterNations\Component\Solr\Expr\TermExpr;
use InterNations\Component\Solr\Expr\WildcardExpr;
use InterNations\Component\Solr\Expr\GroupExpr;
use InterNations\Component\Solr\Expr\BoostExpr;
use InterNations\Component\Solr\Expr\FieldExpr;
use InterNations\Component\Solr\Expr\ProximityExpr;
use InterNations\Component\Solr\Expr\RangeExpr;
use InterNations\Component\Solr\Expr\FuzzyExpr;
use InterNations\Component\Solr\Expr\BooleanExpr;
use DateTime;
use DateTimeZone;

class ExprTest extends AbstractTestCase
{
    public function testTermExpr()
    {
        $this->assertSame('"foo\:bar"', (string) new TermExpr('foo:bar'));
        $this->assertSame('"foo"', (string) new TermExpr('foo'));
        $this->assertSame('"völ"', (string) new TermExpr('völ', 10));
        $this->assertSame('"val1"', (string) new TermExpr('val1'));
        $this->assertSame('"foo bar"', (string) new TermExpr('foo bar'));
    }

    public function testWildcardExprEscapesSuffixAndPrefixButNotWildcard()
    {
        $this->assertSame('foo\:bar?bar', (string) new WildcardExpr('?', 'foo:bar', 'bar'));
        $this->assertSame('foo*bar\:foo', (string) new WildcardExpr('*', 'foo', 'bar:foo'));
        $this->assertSame('foo\:bar?', (string) new WildcardExpr('?', 'foo:bar'));
    }

    public function testPhrasesAndWildcards()
    {
        $this->assertSame('"foo bar*baz"', (string) new WildcardExpr('*', new TermExpr('foo bar'), 'baz'));
        $this->assertSame('"foo bar\:baz*baz"', (string) new WildcardExpr('*', new TermExpr('foo bar:baz'), 'baz'));
    }

    public function testGroupingPhrasesAndTerms()
    {
        $this->assertSame('(foo\:bar "foo bar")', (string) new GroupExpr(['foo:bar', new TermExpr('foo bar')]));
        $this->assertSame(
            '(foo* "foo bar")',
            (string) new GroupExpr([new WildcardExpr('*', 'foo'), new TermExpr('foo bar')])
        );
        $this->assertSame('', (string) new GroupExpr([]));
        $this->assertSame('("foo bar")', (string) new GroupExpr([null, false, '', new TermExpr('foo bar')]));
    }

    public function testBoostingPhrasesTermsAndGroups()
    {
        $this->assertSame('foo^10', (string) new BoostExpr(10, 'foo'));
        $this->assertSame('foo^10', (string) new BoostExpr('10dsfsd', 'foo'));
        $this->assertSame('foo^10.2', (string) new BoostExpr('10.2dsfsd', 'foo'));
        $this->assertSame('foo^10.1', (string) new BoostExpr(10.1, 'foo'));
        $this->assertSame('foo*^200', (string) new BoostExpr(200, new WildcardExpr('*', 'foo')));
        $this->assertSame('(foo bar)^200', (string) new BoostExpr(200, new GroupExpr(['foo', 'bar'])));
    }

    public function testFieldExpr()
    {
        $this->assertSame('field:value\:foo', (string) new FieldExpr('field', 'value:foo'));
        $this->assertSame(
            'field:(foo "foo bar")',
            (string) new FieldExpr('field', new GroupExpr(['foo', new TermExpr('foo bar')]))
        );
        $this->assertSame('fie\-ld:foo', (string) new FieldExpr('fie-ld', 'foo'));
    }

    public function testBooleanExpr()
    {
        $this->assertSame(
            '+(foo bar)',
            (string) new BooleanExpr(BooleanExpr::OPERATOR_REQUIRED, new GroupExpr(['foo', 'bar']))
        );
        $this->assertSame(
            '+"foo bar"',
            (string) new BooleanExpr(BooleanExpr::OPERATOR_REQUIRED, new TermExpr('foo bar'))
        );
        $this->assertSame('+foo', (string) new BooleanExpr(BooleanExpr::OPERATOR_REQUIRED, 'foo'));
        $this->assertSame(
            '+foo?bar',
            (string) new BooleanExpr(BooleanExpr::OPERATOR_REQUIRED, new WildcardExpr('?', 'foo', 'bar'))
        );
        $this->assertSame('-foo', (string) new BooleanExpr(BooleanExpr::OPERATOR_PROHIBITED, 'foo'));
        $this->assertSame(
            '-"foo bar"',
            (string) new BooleanExpr(BooleanExpr::OPERATOR_PROHIBITED, new TermExpr('foo bar'))
        );
        $this->assertSame(
            '-"foo?bar baz"',
            (string) new BooleanExpr(
                BooleanExpr::OPERATOR_PROHIBITED,
                new WildcardExpr('?', 'foo', new TermExpr('bar baz'))
            )
        );
    }

    public function testProximityExpr()
    {
        $this->assertSame('"foo bar"~100', (string) new ProximityExpr('foo', 'bar', 100));
        $this->assertSame('"bar foo"~200', (string) new ProximityExpr('bar', 'foo', 200));
    }

    public function testRangeExpr()
    {
        $this->assertSame('[foo TO bar]', (string) new RangeExpr('foo', 'bar', true));
        $this->assertSame('[foo TO bar]', (string) new RangeExpr('foo', 'bar'));
        $this->assertSame('[foo TO "foo bar"]', (string) new RangeExpr('foo', new TermExpr('foo bar')));
        $this->assertSame('{foo TO "foo bar"}', (string) new RangeExpr('foo', new TermExpr('foo bar'), null, false));
        $this->assertSame(
            '{foo TO "foo bar?"}',
            (string) new RangeExpr('foo', new WildcardExpr('?', new TermExpr('foo bar')), false)
        );
    }

    public function testFuzzyExpr()
    {
        $this->assertSame('foo~', (string) new FuzzyExpr('foo'));
        $this->assertSame('foo~0.8', (string) new FuzzyExpr('foo', 0.8));
        $this->assertSame('foo~0', (string) new FuzzyExpr('foo', 0));
    }

    public function testDateExpr()
    {
        $this->assertSame(
            '2012-12-13T14:15:16Z',
            (string) new DateTimeExpr(new DateTime('2012-12-13 15:15:16', new DateTimeZone('Europe/Berlin')))
        );
    }

    public function testGroupExpr()
    {
        $this->assertSame('(1 2 3)', (string) new GroupExpr([1, 2, 3]));
        $this->assertSame('(one two three)', (string) new GroupExpr(['one', 'two', 'three']));
        $this->assertSame('(one\: two three)', (string) new GroupExpr(['one:', 'two', 'three']));
        $this->assertSame('("one two" "three four")', (string) new GroupExpr(['one two', 'three four']));
    }

    public function testPlaceholderReplacement()
    {
        $expr = new Expr('field:<placeholder>');
        $expr->setPlaceholder('placeholder', 'foo bar');

        $this->assertSame('field:"foo bar"', (string) $expr);
    }

    public function testPlaceholderReplacement_Escapes()
    {
        $expr = new Expr('field:<placeholder>');
        $expr->setPlaceholder('placeholder', 'foo:bar');

        $this->assertSame('field:"foo\:bar"', (string) $expr);
    }

    public function testPlaceholderReplacement_MultiplePlaceholders()
    {
        $expr = new Expr('field1:<p1> AND field2:<p2>');
        $expr->setPlaceholder('p1', '?')
            ->setPlaceholder('p2', '*');

        $this->assertSame('field1:"\?" AND field2:"\*"', (string) $expr);
    }

    public function testPlaceholderReplacement_WithExpressions()
    {
        $expr = new Expr('field:<p>');
        $expr->setPlaceholder('p', new WildcardExpr('*'));

        $this->assertSame('field:*', (string) $expr);
    }

    public function testPlaceholderReplacement_DateTime()
    {
        $expr = new Expr('field:<p>');
        $expr->setPlaceholder('p', new DateTime('2012-12-13 14:15:16'));

        $this->assertSame('field:2012-12-13T13:15:16Z', (string) $expr);
    }

    public function testPlaceholderReplacement_Array()
    {
        $expr = new Expr('field:<p>');
        $expr->setPlaceholder('p', [1,2,3]);

        $this->assertSame('field:(1 2 3)', (string) $expr);
    }
}
