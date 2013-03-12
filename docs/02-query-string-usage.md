# Query String API

An alternative way to construct Lucene/Solr/ElasticSearch queries is the Query String API. It provides a string based
API that is well suited for more static queries. The cool thing about it is the placeholder API. Let’s create a query to
search for products containing "product name" in the name, "TEST1" or "TEST2" as SKU: `name:"product name" AND
sku:("TEST1" "TEST2") AND createdOn:[2012-01-01T00:00:00Z TO 2012-01-01T:23:59:59Z]"`). We also show how to demonstrate
how to use expression buider together with the query string API.

```php
$query = (new QueryString('name:<value> AND sku:<list> AND createdOn:<range>'))
    ->setPlaceholder('value', 'product name')
    ->setPlaceholder('sku', ['TEST1', 'TEST2'])
    ->setPlaceholder($eb->dateRange(new DateTime('2012-01-01 00:00:00'), new DateTime('2012-01-01 23:59:59')));
```
