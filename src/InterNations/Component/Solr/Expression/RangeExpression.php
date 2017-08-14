<?php
namespace InterNations\Component\Solr\Expression;

use InterNations\Component\Solr\ExpressionInterface;
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
     * @var string|integer|Expression
     */
    protected $start;

    /**
     * End of the range
     *
     * @var string|integer|Expression
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
     * @param string|integer|Expression $start
     * @param string|integer|Expression $end
     */
    public function __construct($start = null, $end = null, bool $inclusive = true)
    {
        $this->start = $start;
        $this->end = $end;
        $this->inclusive = (bool) $inclusive;
    }

    public function __toString(): string
    {
        return sprintf(
            '%s%s TO %s%s',
            $this->inclusive ? '[' : '{',
            $this->cast($this->start),
            $this->cast($this->end),
            $this->inclusive ? ']' : '}'
        );
    }

    /**
     * @param ExpressionInterface|string|null $value
     * @return ExpressionInterface|string
     */
    private function cast($value)
    {
        return $value === null ? '*' : Util::sanitize($value);
    }
}
