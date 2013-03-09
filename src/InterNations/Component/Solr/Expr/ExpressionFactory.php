<?php
namespace InterNations\Component\Solr\Expr;

use DateTime;
use InterNations\Component\Solr\Util;

final class ExpressionFactory
{
    public static function createExpression($value)
    {
        if ($value instanceof Expr) {
            return $value;
        }

        if ($value === null) {
            return new WildcardExpr('*');
        }

        if (is_array($value)) {
            return new GroupExpr($value);
        }

        if ($value instanceof DateTime) {
            return new DateTimeExpr($value);
        }

        if (!preg_match('/\s/', $value)) {
            return new TermExpr($value);
        }

        return new PhraseExpr($value);
    }
}
