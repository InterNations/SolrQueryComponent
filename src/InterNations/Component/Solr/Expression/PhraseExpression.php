<?php
namespace InterNations\Component\Solr\Expression;

use InterNations\Component\Solr\Util;

/**
 * Class for query phrases
 *
 * Phrases are grouped terms for exact matching in the like of "word1 word2"
 */
class PhraseExpression extends Expression
{
    public function __toString()
    {
        return Util::quote($this->expr);
    }
}
