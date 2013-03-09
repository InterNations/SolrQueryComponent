<?php
namespace InterNations\Component\Solr\Expr;

use InterNations\Component\Solr\Util;

/**
 * Boolean expression class
 *
 * Class to construct boolean queries (+<term> or -<term>)
 */
class BooleanExpr extends Expr
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
     * @param string|Expr $expr
     */
    public function __construct($operator, $expr)
    {
        $this->operator = $operator;
        parent::__construct($expr);
    }

    /**
     * @inherited
     * @return string
     */
    public function __toString()
    {
        return $this->operator . Util::escape($this->expr);
    }
}
