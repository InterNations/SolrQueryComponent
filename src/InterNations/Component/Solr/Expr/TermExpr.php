<?php
namespace InterNations\Component\Solr\Expr;

use InterNations\Component\Solr\Util;

/**
 * Expression class representing a single, escaped term
 */
class TermExpr extends Expr
{
    public function __toString()
    {
        return Util::escape((string) $this->expr);
    }
}
