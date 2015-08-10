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
     * Use the NOT notation: (*:* NOT <expr>), e.g. (*:* NOT fieldName:*)
     *
     * @var boolean
     */
    protected $useNotNotation;

    /**
     * Create new expression object
     *
     * @param string $operator
     * @param string|Expression $expr
     * @param boolean $useNotNotation use the NOT notation: (*:* NOT <expr>), e.g. (*:* NOT fieldName:*)
     */
    public function __construct($operator, $expr, $useNotNotation = false)
    {
        $this->operator = $operator;
        $this->useNotNotation = $useNotNotation;
        parent::__construct($expr);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->useNotNotation
            ? '(*:* NOT ' . Util::escape($this->expr) . ')'
            : $this->operator . Util::escape($this->expr);
    }
}
