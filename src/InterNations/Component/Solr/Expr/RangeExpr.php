<?php
namespace InterNations\Component\Solr\Expr;

use InterNations\Component\Solr\Util;

/**
 * Range expression class
 *
 * Let you specify range queries in the like of field:[<start> TO <end>] or field:{<start> TO <end>}
 */
class RangeExpr extends Expr
{
    /**
     * Start of the range
     *
     * @var string|int|Expr
     */
    protected $start;

    /**
     * End of the range
     *
     * @var string|int|Expr
     */
    protected $end;

    /**
     * Inclusive or exclusive the range start/end?
     *
     * @var boolean
     */
    protected $inclusive;

    /**
     * Create new range query object
     *
     * @param string|int|Expr $start
     * @param string|int|Expr $end
     * @param boolean $inclusive
     */
    public function __construct($start = null, $end = null, $inclusive = true)
    {
        $this->start = $start;
        $this->end = $end;
        $this->inclusive = (bool) $inclusive;
    }

    /**
     * @inherited
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            '%s%s TO %s%s',
            $this->inclusive ? '[' : '{',
            Util::escape($this->start)  ?: '*',
            Util::escape($this->end)  ?: '*',
            $this->inclusive ? ']' : '}'
        );
    }
}
