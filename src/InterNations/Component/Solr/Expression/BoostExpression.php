<?php
namespace InterNations\Component\Solr\Expression;

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
     * @var float|int
     */
    protected $boost;

    /**
     * @param float|int $boost
     * @param string|Expression $expr
     */
    public function __construct($boost, $expr)
    {
        $this->boost = is_int($boost) ? $boost : (float) $boost;
        parent::__construct($expr);
    }

    public function __toString()
    {
        return Util::sanitize($this->expr) . '^' . $this->boost;
    }
}
