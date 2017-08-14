<?php
namespace InterNations\Component\Solr\Expression;

use InterNations\Component\Solr\ExpressionInterface;
use InterNations\Component\Solr\Util;

/**
 * Boolean expression class
 *
 * Class to construct boolean queries (+<term> or -<term>)
 */
class BooleanExpression extends Expression
{
    public const OPERATOR_REQUIRED = '+';
    public const OPERATOR_PROHIBITED = '-';

    /**
     * Boolean operand
     *
     * @var string
     */
    private $operator;

    /**
     * Use the NOT notation: (*:* NOT <expr>), e.g. (*:* NOT fieldName:*)
     *
     * @var boolean
     */
    private $useNotNotation;

    /**
     * Create new expression object
     *
     * @param ExpressionInterface|string $expr
     * @param boolean $useNotNotation use the NOT notation: (*:* NOT <expr>), e.g. (*:* NOT fieldName:*)
     */
    public function __construct(string $operator, $expr, bool$useNotNotation = false)
    {
        $this->operator = $operator;
        $this->useNotNotation = $useNotNotation;
        parent::__construct($expr);
    }

    public function __toString(): string
    {
        return $this->useNotNotation
            ? '(*:* NOT ' . Util::escape($this->expr) . ')'
            : $this->operator . Util::escape($this->expr);
    }
}
