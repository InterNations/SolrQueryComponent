<?php
namespace InterNations\Component\Solr\Expression;

/**
 * Group expression class
 *
 * Class representing expressions grouped together in the like of (term1 term2).
 */
class GroupExpression extends CompositeExpression
{
    public function __toString()
    {
        $part = parent::__toString();

        if (!$part) {
            return $part;
        }

        return '(' . $part . ')';
    }
}
