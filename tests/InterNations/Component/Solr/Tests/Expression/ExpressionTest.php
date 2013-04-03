<?php
namespace InterNations\Component\Solr\Tests\Expression;

use InterNations\Component\Solr\Expression\FunctionExpression;
use InterNations\Component\Solr\Expression\GeolocationExpression;
use InterNations\Component\Solr\Expression\LocalParamsExpression;
use InterNations\Component\Solr\Expression\ParameterExpression;
use InterNations\Component\Testing\AbstractTestCase;
use InterNations\Component\Solr\Expression\DateTimeExpression;
use InterNations\Component\Solr\Expression\PhraseExpression;
use InterNations\Component\Solr\Expression\WildcardExpression;
use InterNations\Component\Solr\Expression\GroupExpression;
use InterNations\Component\Solr\Expression\BoostExpression;
use InterNations\Component\Solr\Expression\FieldExpression;
use InterNations\Component\Solr\Expression\ProximityExpression;
use InterNations\Component\Solr\Expression\RangeExpression;
use InterNations\Component\Solr\Expression\FuzzyExpression;
use InterNations\Component\Solr\Expression\BooleanExpression;
use DateTime;
use DateTimeZone;

class ExpressionTest extends AbstractTestCase
{
    public function testPhraseExpression()
    {
        $this->assertSame('"foo\:bar"', (string) new PhraseExpression('foo:bar'));
        $this->assertSame('"foo"', (string) new PhraseExpression('foo'));
        $this->assertSame('"völ"', (string) new PhraseExpression('völ', 10));
        $this->assertSame('"val1"', (string) new PhraseExpression('val1'));
        $this->assertSame('"foo bar"', (string) new PhraseExpression('foo bar'));
    }

    public function testWildcardExprEscapesSuffixAndPrefixButNotWildcard()
    {
        $this->assertSame('foo\:bar?bar', (string) new WildcardExpression('?', 'foo:bar', 'bar'));
        $this->assertSame('foo*bar\:foo', (string) new WildcardExpression('*', 'foo', 'bar:foo'));
        $this->assertSame('foo\:bar?', (string) new WildcardExpression('?', 'foo:bar'));
    }

    public function testPhrasesAndWildcards()
    {
        $this->assertSame('"foo bar*baz"', (string) new WildcardExpression('*', new PhraseExpression('foo bar'), 'baz'));
        $this->assertSame('"foo bar\:baz*baz"', (string) new WildcardExpression('*', new PhraseExpression('foo bar:baz'), 'baz'));
    }

    public function testGroupingPhrasesAndTerms()
    {
        $this->assertSame('("foo\:bar" "foo bar")', (string) new GroupExpression(['foo:bar', new PhraseExpression('foo bar')]));
        $this->assertSame(
            '(foo* "foo bar")',
            (string) new GroupExpression([new WildcardExpression('*', 'foo'), new PhraseExpression('foo bar')])
        );
        $this->assertSame('', (string) new GroupExpression([]));
        $this->assertSame('("foo bar")', (string) new GroupExpression([null, false, '', new PhraseExpression('foo bar')]));
    }

    public function testBoostingPhrasesTermsAndGroups()
    {
        $this->assertSame('"foo"^10', (string) new BoostExpression(10, 'foo'));
        $this->assertSame('"foo"^10', (string) new BoostExpression('10dsfsd', 'foo'));
        $this->assertSame('"foo"^10.2', (string) new BoostExpression('10.2dsfsd', 'foo'));
        $this->assertSame('"foo"^10.1', (string) new BoostExpression(10.1, 'foo'));
        $this->assertSame('foo*^200', (string) new BoostExpression(200, new WildcardExpression('*', 'foo')));
        $this->assertSame('("foo" "bar")^200', (string) new BoostExpression(200, new GroupExpression(['foo', 'bar'])));
    }

    public function testFieldExpression()
    {
        $this->assertSame('field:"value\:foo"', (string) new FieldExpression('field', 'value:foo'));
        $this->assertSame(
            'field:("foo" "foo bar")',
            (string) new FieldExpression('field', new GroupExpression(['foo', new PhraseExpression('foo bar')]))
        );
        $this->assertSame('fie\-ld:"foo"', (string) new FieldExpression('fie-ld', 'foo'));
    }

    public function testBooleanExpression()
    {
        $this->assertSame(
            '+("foo" "bar")',
            (string) new BooleanExpression(BooleanExpression::OPERATOR_REQUIRED, new GroupExpression(['foo', 'bar']))
        );
        $this->assertSame(
            '+"foo bar"',
            (string) new BooleanExpression(BooleanExpression::OPERATOR_REQUIRED, new PhraseExpression('foo bar'))
        );
        $this->assertSame('+foo', (string) new BooleanExpression(BooleanExpression::OPERATOR_REQUIRED, 'foo'));
        $this->assertSame(
            '+foo?bar',
            (string) new BooleanExpression(BooleanExpression::OPERATOR_REQUIRED, new WildcardExpression('?', 'foo', 'bar'))
        );
        $this->assertSame('-foo', (string) new BooleanExpression(BooleanExpression::OPERATOR_PROHIBITED, 'foo'));
        $this->assertSame(
            '-"foo bar"',
            (string) new BooleanExpression(BooleanExpression::OPERATOR_PROHIBITED, new PhraseExpression('foo bar'))
        );
        $this->assertSame(
            '-"foo?bar baz"',
            (string) new BooleanExpression(
                BooleanExpression::OPERATOR_PROHIBITED,
                new WildcardExpression('?', 'foo', new PhraseExpression('bar baz'))
            )
        );
    }

    public function testProximityExpression()
    {
        $this->assertSame('"foo bar"~100', (string) new ProximityExpression(['foo', 'bar'], 100));
        $this->assertSame('"bar foo"~200', (string) new ProximityExpression(['bar', 'foo'], 200));
    }

    public function testRangeExpression()
    {
        $this->assertSame('["foo" TO "bar"]', (string) new RangeExpression('foo', 'bar', true));
        $this->assertSame('["foo" TO "bar"]', (string) new RangeExpression('foo', 'bar'));
        $this->assertSame('["foo" TO "foo bar"]', (string) new RangeExpression('foo', new PhraseExpression('foo bar')));
        $this->assertSame('{"foo" TO "foo bar"}', (string) new RangeExpression('foo', new PhraseExpression('foo bar'), null, false));
        $this->assertSame(
            '{"foo" TO "foo bar?"}',
            (string) new RangeExpression('foo', new WildcardExpression('?', new PhraseExpression('foo bar')), false)
        );
        $this->assertSame('[-1 TO 0]', (string) new RangeExpression(-1, 0));
        $this->assertSame('[0 TO 1]', (string) new RangeExpression(0, 1));
    }

    public function testFuzzyExpression()
    {
        $this->assertSame('foo~', (string) new FuzzyExpression('foo'));
        $this->assertSame('foo~0.8', (string) new FuzzyExpression('foo', 0.8));
        $this->assertSame('foo~0', (string) new FuzzyExpression('foo', 0));
    }

    public function testDateExpression()
    {
        $this->assertSame(
            '2012-12-13T14:15:16Z',
            (string) new DateTimeExpression(new DateTime('2012-12-13 15:15:16', new DateTimeZone('Europe/Berlin')))
        );
        $this->assertSame(
            '2012-12-13T14:15:16Z',
            (string) new DateTimeExpression(new DateTime('2012-12-13 11:15:16', new DateTimeZone('Europe/Berlin')), null, 'Europe/Moscow')
        );
        $this->assertSame(
            '2012-12-13T14:15:16Z',
            (string) new DateTimeExpression(new DateTime('2012-12-13 14:15:16', new DateTimeZone('Europe/Berlin')), null, null)
        );
    }

    public function testGroupExpression()
    {
        $this->assertSame('(1 2 3)', (string) new GroupExpression([1, 2, 3]));
        $this->assertSame('("one" "two" "three")', (string) new GroupExpression(['one', 'two', 'three']));
        $this->assertSame('("one\:" "two" "three")', (string) new GroupExpression(['one:', 'two', 'three']));
        $this->assertSame('("one two" "three four")', (string) new GroupExpression(['one two', 'three four']));

        $this->assertSame('(1 AND 2 AND 3)', (string) new GroupExpression([1, 2, 3], GroupExpression::TYPE_AND));
        $this->assertSame('(1 OR 2 OR 3)', (string) new GroupExpression([1, 2, 3], GroupExpression::TYPE_OR));
    }

    public function testFunctionExpression()
    {
        $this->assertSame('sum(1, 2, 3, "text")', (string) new FunctionExpression('sum', [1, 2, 3, "text"]));
        $this->assertSame('func(1)', (string) new FunctionExpression('func', [1]));
        $this->assertSame('func()', (string) new FunctionExpression('func'));
        $this->assertSame('func()', (string) new FunctionExpression('func', null));
        $this->assertSame('func()', (string) new FunctionExpression('func', []));
        $this->assertSame('func()', (string) new FunctionExpression('func', new ParameterExpression([])));
        $this->assertSame(
            'func("foo", "bar", 1)',
            (string) new FunctionExpression('func', new ParameterExpression(['foo', 'bar', 1]))
        );
    }

    public function testLocalParams()
    {
        $this->assertSame('{!func}', (string) new LocalParamsExpression('func'));
        $this->assertSame('{!func}', (string) new LocalParamsExpression('func', [], true));
        $this->assertSame('{!type=func}', (string) new LocalParamsExpression('func', [], false));

        $this->assertSame('{!dismax qf="myfield"}', (string) new LocalParamsExpression('dismax', ['qf' => 'myfield']));
    }

    public function testGeolocationExpression()
    {
        $this->assertSame('12.345678901234,89.012345670000', (string) new GeolocationExpression(12.345678901234, 89.01234567, 12));
        $this->assertSame('12.345678900000,89.012345678901', (string) new GeolocationExpression(12.34567890, 89.012345678901, 12));

        $this->assertSame('12.3457,89.0123', (string) new GeolocationExpression(12.345678901234, 89.01234567, 4));
    }
}
