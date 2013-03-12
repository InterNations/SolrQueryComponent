# Integration

There are various ways to integrate `SolrQueryComponent` with your custom query facades. The following examples will
assume some kind of a `SearchService`, that acts as a Facade to access the Solr search index.


We start with a simple base class for search services in general that define methods to create an instance of
`ExpressionBuilder` and new instances of `QueryString`. We assume further, that we use
[http://pecl.php.net/package/solr](pecl/solr) as a client.

```php
<?php
namespace Acme\Search;

// SolrQueryComponent classes
use InterNations\Component\Solr\Expression\ExpressionBuilder;
use InterNations\Component\Solr\Query\QueryString;
// PECL Solr classes
use SolrClient;
use SolrQuery;

abstract class AbstractSearchService
{
    private $expressionBuilder;

    protected $client;

    public function __construct(SolrClient $client)
    {
        $this->client = $client;
    }

    protected function createQuery()
    {
        return new SolrQuery();
    }

    protected function createExpressionBuilder()
    {
        // Expression builder is stateless, so we can reuse the same instance over and over again
        if (!$this->expressionBuilder) {
            $this->expressionBuilder = new ExpressionBuilder();
        }
        return new $this->expressionBuilder;
    }

    protected function createQueryString($query)
    {
        return new QueryString($query);
    }
}
```

A concrete search service will extend the abstract base class and add index specific search methods.

```php
<?php
namespace Acme\Search;

class CatalogSearchService extends AbstractSearchService
{
    public function searchByName($searchString, $rows, $start = 0)
    {
        $searchQuery = $this->createQueryString('productName:<name>')
            ->setPlaceholder('name', $searchString);

        $eb = $this->createExpressionBuilder();
        $filterQuery = $eb->req($eb->comp($eb->field('disabled', 0)), $eb->field('visible', 1));

        $query = $this->createQuery()
            ->setQuery($searchQuery)
            ->addFilterQuery($filterQuery)
            ->setStart($start)
            ->setRows($rows);

        return $this->client->query($query);
    }
}
```
