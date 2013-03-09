<?php
namespace InterNations\Component\Solr\Expr;

use InterNations\Component\Solr\Util;

/**
 * Class for query phrases
 *
 * Phrases are grouped terms for exact matching in the like of "word1 word2"
 */
class PhraseExpression extends Expression
{
    /**
     * @param string $expr
     */
    public function __construct($expr)
    {
        parent::__construct(mb_strtolower($expr, 'UTF-8'));
    }

    public function __toString()
    {
        return Util::quote($this->expr);
    }
}
