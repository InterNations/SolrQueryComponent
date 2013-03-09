<?php
namespace InterNations\Component\Solr\Expression;

use InterNations\Component\Solr\Util;

/**
 * Base class for expressions
 *
 * The base class for query expressions provides methods to escape and quote query strings as well being the object to
 * create literal queries which should not be escaped
 */
class Expression
{
    /**
     * Expression object or string
     *
     * @var Expression|string
     */
    protected $expr;

    /**
     * @var array
     */
    private $placeholders = [];

    /**
     * Create new expression object
     *
     * @param Expression|string $expr
     */
    public function __construct($expr)
    {
        $this->expr = $expr;
    }

    /**
     * @param string $placeholder
     * @param mixed $value
     * @return self
     */
    public function setPlaceholder($placeholder, $value)
    {
        $this->placeholders[$placeholder] = $value;

        return $this;
    }

    /**
     * @param array $placeholders
     * @return self
     */
    public function setPlaceholders(array $placeholders)
    {
        $this->placeholders = $placeholders;

        return $this;
    }

    public function getPlaceholders()
    {
        return $this->placeholders;
    }

    /**
     * Returns true if given expression is equal
     *
     * @param Expression|string $expr
     * @return boolean
     */
    public function isEqual($expr)
    {
        return (string) $expr === (string) $this;
    }

    /**
     * Return string representation
     *
     * @return string
     */
    public function __toString()
    {
        return $this->replacePlaceholders($this->expr);
    }

    protected function replacePlaceholders($expr)
    {
        $replacements = [];
        foreach ($this->placeholders as $placeholder => $value) {
            $replacements['<' . $placeholder . '>'] = ExpressionFactory::createExpression($value);
        }

        return strtr($expr, $replacements);
    }
}
