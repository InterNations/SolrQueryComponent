# SolrQueryComponent: Build Solr queries with ease

`SolrQueryComponent` helps building Solr/Lucene/ElasticSearch queries with a query builder API. It is independent of
the concrete client library and can be used with e.g. [PECL Solr](http://pecl.php.net/package/solr) or
[Solarium](http://www.solarium-project.org/).

### Examples

Build `name:"John Doe"^100`

```php
<?php
$eb = new InterNations\Component\Solr\Expression\ExpressionBuilder();
echo $eb->field('name', $eb->boost($eb->eq('John Doe'), 100));
```
