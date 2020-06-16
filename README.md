# SolrQueryComponent

[![Build Status](https://travis-ci.org/InterNations/SolrQueryComponent.svg)](https://travis-ci.org/InterNations/SolrQueryComponent)

#### Build Solr queries with ease

`SolrQueryComponent` helps building Solr/Lucene/ElasticSearch queries with a query builder API. It is independent of
the concrete client library and can be used with e.g. [PECL Solr](http://pecl.php.net/package/solr) or
[Solarium](http://www.solarium-project.org/).

### Examples

Build `name:"John Doe"^100`

```php
<?php
use InterNations\Component\Solr\Expression\ExpressionBuilder;

$eb = new ExpressionBuilder();
echo $eb->field('name', $eb->boost($eb->eq('John Doe'), 100));
```

And the same with the query string object:

```php
<?php
use InterNations\Component\Solr\Query\QueryString;

echo (new QueryString('name:<name>^<boost>'))
    ->setPlaceholder('name', 'John Doe')
    ->setPlaceholder('boost', 100);
```

Learn more on how to use the component in [docs/](docs).
