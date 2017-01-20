<?php
namespace InterNations\Component\Solr\Expression;

use InterNations\Component\Solr\Util;

/**
 * Field query expression
 *
 * Class representing a query limited to specific fields (field:<value>)
 */
class FieldExpression extends Expression
{
    /**
     * Field name
     *
     * @var string
     */
    protected $field;

    /**
     * Create new field query
     *
     * @param string $field
     * @param string|Expression $expr
     */
    public function __construct($field, $expr)
    {
        $this->field = $field;
        parent::__construct($expr);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $field = Util::escape($this->field);
        $expression = Util::sanitize($this->expr);

        if ($this->expr instanceof LocalParamsExpression) {
            return $expression . $field;
        }

        return  $field . ':' . $expression;
    }
}
