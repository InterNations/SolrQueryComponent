<?php
namespace InterNations\Component\Solr\Expression;

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
        $query = '';

        $parts = array_map(['InterNations\Component\Solr\Util', 'sanitize'], $this->expressions);

        if ($parts) {
            $query = '(' . join(' ', array_filter($parts)) . ')';
        }

        return $query;
    }
}
