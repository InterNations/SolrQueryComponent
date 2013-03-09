<?php
namespace InterNations\Component\Solr\Expression;

use DateTime;

final class ExpressionFactory
{
    public static function createExpression($value)
    {
        if ($value instanceof Expression) {
            return $value;
        }

        if ($value === null) {
            return new WildcardExpression('*');
        }

        if (is_array($value)) {
            return new GroupExpression($value);
        }

        if ($value instanceof DateTime) {
            return new DateTimeExpression($value);
        }

        return new PhraseExpression($value);
    }
}
