# Expression Builder

The expression builder API is designed to allow programmatic generation of queries. It mimics the API of other well
known builder domains, such as SQL. It additionally escapes all user input and makes sure you don’t accidentally
introduce query injections. While the query API of Solr or ElasticSearch is read only and injections will not lead to
data destruction, you could still leak private information (such as products not yet published).

## Simple queries

Constructing simple queries is quite easy. Given that you have configured a default search field or pass one with the
query, all you need to do is to call the `eq()` method. Let’s construct a simple query that consists of a single phrase
`"this is my search term"`.

```php
$query = $eb->eq('this is my search term');
```

Next step might be to limit those searches to a specific field (`myField:"this is my search term"`).

```php
$query = $eb->field('myField', $eb->eq('this is my search term'));
```
