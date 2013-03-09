# SolrQueryComponent: Build Solr queries with ease

### Examples

Build `name:"John Doe"^100`

```php
<?php
$eb = new InterNations\Component\Solr\Expression\ExpressionBuilder();
echo $eb->field('name', $eb->boost($eb->eq('John Doe'), 100));
```
