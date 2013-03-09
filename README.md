# SolrQueryComponent: Build Solr queries with ease

### Examples

Build `name:"John Doe"^100`

```php
<?php
$expr = new InterNations\Component\Solr\Expr\ExprBuilder();
echo $expr->field('name', $expr->boost($expr->eq('John Doe'), 100));
```
