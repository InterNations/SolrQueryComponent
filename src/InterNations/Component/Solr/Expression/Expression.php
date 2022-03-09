<?php
namespace InterNations\Component\Solr\Expression;

use InterNations\Component\Solr\ExpressionInterface;

/**
 * Base class for expressions
 *
 * The base class for query expressions provides methods to escape and quote query strings as well being the object to
 * create literal queries which should not be escaped
 */
class Expression implements ExpressionInterface
{
    /**
     * Expression object or string
     *
     * @var ExpressionInterface|string
     */
    protected $expr;

    /**
     * Create new expression object
     *
     * @param ExpressionInterface|string $expr
     */
    public function __construct($expr)
    {
        $this->expr = $expr;
    }

    public function isEqual(string $expr): bool
    {
        return $expr === (string) $this;
    }

    public function __toString(): string
    {
        return (string) $this->expr;
    }
}
