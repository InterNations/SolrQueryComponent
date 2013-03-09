<?php
namespace InterNations\Component\Solr\Expression;

use InterNations\Component\Solr\Util;

/**
 * Group expression class
 *
 * Class representing expressions grouped together in the like of (term1 term2).
 */
class GroupExpression extends Expression
{
    /**
     * List of query expressions
     *
     * @var array
     */
    protected $expressions = [];

    /**
     * Create new group of expression
     *
     * @param array $expressions
     */
    public function __construct(array $expressions)
    {
        $this->expressions = $expressions;
    }

    public function __toString()
    {
        $parts = [];
        foreach ($this->expressions as $expression) {
            if (!$expression) {
                continue;
            }

            $parts[] = Util::sanitize($expression);
        }

        if (!$parts) {
            return '';
        }

        return '(' . join(' ', array_filter($parts)) . ')';
    }
}
