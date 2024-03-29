<?php
namespace InterNations\Component\Solr\Tests\Query;

use InterNations\Component\Solr\Expression\RangeExpression;
use InterNations\Component\Solr\Query\QueryString;
use InterNations\Component\Solr\Expression\GroupExpression;
use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

class QueryStringTest extends TestCase
{
    public function testSimpleQuery(): void
    {
        $query = new QueryString('field:value');
        $this->assertSame('field:value', (string) $query);
    }

    public function testQueryWithPlaceholder_String(): void
    {
        $query = new QueryString('field:<ph>');
        $this->assertSame('field:<ph>', (string) $query);

        $query->setPlaceholder('ph', 'text');
        $this->assertSame('field:"text"', (string) $query);
    }

    public function testPlaceholderSettersReturnItself(): void
    {
        $query = new QueryString('test');
        $this->assertSame($query, $query->setPlaceholder('foo', 'bar'));
        $this->assertSame($query, $query->setPlaceholders(['foo' => 'bar']));
    }

    public function testQueryWithPlaceholder_Group(): void
    {
        $query = new QueryString('field:<ph>');
        $query->setPlaceholder('ph', new GroupExpression(range(0, 3)));

        $this->assertSame('field:(1 2 3)', (string) $query);
    }

    public function testQueryWithPlaceholder_Date(): void
    {
        $from = new DateTime('2012-10-11 09:08:07', new DateTimeZone('UTC'));
        $to = new DateTime('2013-12-11 10:09:08', new DateTimeZone('UTC'));

        $query = new QueryString('field:[<from> TO <to>]');
        $query->setPlaceholders(compact('from',  'to'));

        $this->assertSame('field:[2012-10-11T09:08:07Z TO 2013-12-11T10:09:08Z]', (string) $query);
    }

    public function testQueryWithPlaceholder_Array(): void
    {
        $query = new QueryString('field:<ph>');
        $query->setPlaceholder('ph', [1, 2, 3, 4, 5]);

        $this->assertSame('field:(1 2 3 4 5)', (string) $query);
    }

    public function testQueryWithPlaceholder_Boolean(): void
    {
        $query = new QueryString('field:<ph>');
        $query->setPlaceholder('ph', true);

        $this->assertSame('field:true', (string) $query);
        $query->setPlaceholder('ph', false);
        $this->assertSame('field:false', (string) $query);
    }

    public function testQueryWithPlaceholder_Expression(): void
    {
        $query = new QueryString('field:<ph>');
        $query->setPlaceholder('ph', new RangeExpression(0, 100, false));

        $this->assertSame('field:{0 TO 100}', (string) $query);
    }

    public function testSetPlaceholders(): void
    {
        $query = new QueryString('field:<ph>');
        $query->setPlaceholders(['ph' => 'text']);

        $this->assertSame('field:"text"', (string) $query);
    }
}
