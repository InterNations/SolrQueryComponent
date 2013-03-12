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

A popular use case is to fall back on everything. To support that, expression builder has an `all()` method that returns
a `*:*` if all values passed to it are empty. `$query1` will contain `field:"value"` while `$query2` contain `*:*`.

```php
$query1 = $eb->all($eb->field('field', $eb->eq('value')));
$query2 = $eb->all($eb->field('field', $eb->eq(null)));
```

*Note:* the expression builder API generally assumes `null` to be an empty query.


### Requiring and prohibiting expressions

To create queries that require a certain condition to be true, we need to add a plus sign (`+`) in front of it. Let’s
create a query in the form of `+field:"search term"`.

```php
$eb->req($eb->field('field', $eb->eq('search term')));
```

Let’s negate that expression and make sure we only get documents where `"search term`" is not present (`-field:"search
term"`).

```php
$eb->prhb($eb->field('field', $eb->eq('search term')));
```
