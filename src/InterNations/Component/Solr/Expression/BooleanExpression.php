<?php
namespace InterNations\Component\Solr\Expression;

use InterNations\Component\Solr\Util;

/**
 * Boolean expression class
 *
 * Class to construct boolean queries (+<term> or -<term>)
 */
class BooleanExpression extends Expression
{
    const OPERATOR_REQUIRED = '+';

    const OPERATOR_PROHIBITED = '-';

    /**
     * Boolean operand
     *
     * @var string
     */
    protected $operator;

    /**
     * Create new expression object
     *
     * @param string $operator
     * @param string|Expression $expr
     */
    public function __construct($operator, $expr)
    {
        $this->operator = $operator;
        parent::__construct($expr);
    }

    public function __toString()
    {
        return $this->operator . Util::escape($this->expr);
    }
}
