<?php
namespace InterNations\Component\Solr\Expression;

use InterNations\Component\Solr\Util;

/**
 * Range expression class
 *
 * Let you specify range queries in the like of field:[<start> TO <end>] or field:{<start> TO <end>}
 */
class RangeExpression extends Expression
{
    /**
     * Start of the range
     *
     * @var string|int|Expression
     */
    protected $start;

    /**
     * End of the range
     *
     * @var string|int|Expression
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
     * @param string|int|Expression $start
     * @param string|int|Expression $end
     * @param boolean $inclusive
     */
    public function __construct($start = null, $end = null, $inclusive = true)
    {
        $this->start = $start;
        $this->end = $end;
        $this->inclusive = (boolean) $inclusive;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            '%s%s TO %s%s',
            $this->inclusive ? '[' : '{',
            $this->cast($this->start),
            $this->cast($this->end),
            $this->inclusive ? ']' : '}'
        );
    }

    private function cast($value)
    {
        return $value === null ? '*' : Util::sanitize($value);
    }
}
