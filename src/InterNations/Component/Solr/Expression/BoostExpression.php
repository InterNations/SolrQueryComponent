<?php
namespace InterNations\Component\Solr\Expression;

use InterNations\Component\Solr\ExpressionInterface;
use InterNations\Component\Solr\Util;

/**
 * Class representing boosted queries
 *
 * Class to construct boosted queries in the like of <term>^<boost>
 */
class BoostExpression extends Expression
{
    /**
     * Boost factor
     *
     * @var float
     */
    private $boost;

    /**
     * @param ExpressionInterface|string|null $expr
     * @no-named-arguments
     */
    public function __construct(float $boost, $expr)
    {
        $this->boost = $boost;
        parent::__construct($expr);
    }

    public function __toString(): string
    {
        return Util::sanitize($this->expr) . '^' . $this->boost;
    }
}
