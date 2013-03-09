<?php
namespace InterNations\Component\Solr\Expr;

use InterNations\Component\Solr\Util;

/**
 * Class representing boosted queries
 *
 * Class to construct boosted queries in the like of <term>^<boost>
 */
class BoostExpr extends Expr
{
    /**
     * Boost factor
     *
     * @var float|int
     */
    protected $boost;

    public function __construct($boost, $expr)
    {
        $this->boost = is_int($boost) ? $boost : (float) $boost;
        parent::__construct($expr);
    }

    /**
     * @inherited
     * @return string
     */
    public function __toString()
    {
        return Util::escape($this->expr) . '^' . $this->boost;
    }
}
