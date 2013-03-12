<?php
namespace InterNations\Component\Solr\Tests\Query;

use InterNations\Component\Testing\AbstractTestCase;
use InterNations\Component\Solr\Query\QueryString;
use InterNations\Component\Solr\Expression\GroupExpression;

class QueryStringTest extends AbstractTestCase
{
    public function testSimpleQuery()
    {
        $query = new QueryString('field:value');
        $this->assertSame('field:value', (string) $query);
    }

    public function testQueryWithPlaceholder_String()
    {
        $query = new QueryString('field:<ph>');
        $this->assertSame('field:<ph>', (string) $query);

        $query->setPlaceholder('ph', 'text');
        $this->assertSame('field:"text"', (string) $query);
    }

    public function testQueryWithPlaceholder_Group()
    {
        $query = new QueryString('field:<ph>');
        $query->setPlaceholder('ph', new GroupExpression(range(0, 3)));

        $this->assertSame('field:(1 2 3)', (string) $query);
    }

    public function testSetPlaceholders()
    {
        $query = new QueryString('field:<ph>');
        $query->setPlaceholders(['ph' => 'text']);

        $this->assertSame('field:"text"', (string) $query);
    }
}
