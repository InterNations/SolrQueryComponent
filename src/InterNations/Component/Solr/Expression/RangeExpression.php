<?php
namespace InterNations\Component\Solr\Expression;

use InterNations\Component\Solr\Util;

/**
 * Range expression class
 *
 * Let you specify range queries in the like of field:[<start> TO <end>] or field:{<start> TO <end>} or also mixing inclusive/exclusive:[<start> TO <end>} as of Solr 4.0
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
     * Inclusive or exclusive the range start?
     *
     * @var boolean
     */
    protected $inclusiveFrom;

    /**
     * Inclusive or exclusive the range end?
     *
     * @var boolean
     */
    protected $inclusiveTo;

    /**
     * Create new range query object
     *
     * @param string|integer|Expression $start
     * @param string|integer|Expression $end
     * @param boolean $inclusiveFrom
     * @param boolean $inclusiveTo
     */
    public function __construct($start = null, $end = null, $inclusiveFrom = true, $inclusiveTo = true)
    {
        $this->start = $start;
        $this->end = $end;
        $this->inclusiveFrom = (bool) $inclusiveFrom;
        $this->inclusiveTo = (bool) $inclusiveTo;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            '%s%s TO %s%s',
            $this->inclusiveFrom ? '[' : '{',
            $this->cast($this->start),
            $this->cast($this->end),
            $this->inclusiveTo ? ']' : '}'
        );
    }

    private function cast($value)
    {
        return $value === null ? '*' : Util::sanitize($value);
    }
}
