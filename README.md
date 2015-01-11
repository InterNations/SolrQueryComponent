# SolrQueryComponent

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/InterNations/SolrQueryComponent?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![Build Status](https://travis-ci.org/InterNations/SolrQueryComponent.svg)](https://travis-ci.org/InterNations/SolrQueryComponent) [![Dependency Status](https://www.versioneye.com/user/projects/5347af01fe0d0720b50000b1/badge.png)](https://www.versioneye.com/user/projects/5347af01fe0d0720b50000b1) [![Average time to resolve an issue](http://isitmaintained.com/badge/resolution/InterNations/SolrQueryComponent.svg)](http://isitmaintained.com/project/InterNations/SolrQueryComponent "Average time to resolve an issue") [![Percentage of issues still open](http://isitmaintained.com/badge/open/InterNations/SolrQueryComponent.svg)](http://isitmaintained.com/project/InterNations/SolrQueryComponent "Percentage of issues still open")
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
