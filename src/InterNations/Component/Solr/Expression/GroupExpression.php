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
    const TYPE_AND = 'AND';

    const TYPE_OR = 'OR';

    /**
     * List of query expressions
     *
     * @var array
     */
    private $expressions = [];

    /**
     * @var string
     */
    private $type;

    /**
     * Create new group of expression
     *
     * @param array $expressions
     * @param string $type
     */
    public function __construct(array $expressions, $type = null)
    {
        $this->expressions = $expressions;
        $this->type = $type;
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

        $glue = $this->type ? ' ' . $this->type . ' ' : ' ';

        return '(' . join($glue, array_filter($parts)) . ')';
    }

    public static function isType($type)
    {
        return $type === static::TYPE_OR || $type === static::TYPE_AND || $type === null;
    }
}
