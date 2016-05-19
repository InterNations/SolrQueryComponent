<?php
namespace InterNations\Component\Solr\Expression;

use InterNations\Component\Solr\Util;

/**
 * Composite expression class
 *
 * Class representing multiple expressions with an optional combination type
 */
class CompositeExpression extends Expression
{
    const TYPE_AND = 'AND';

    const TYPE_OR = 'OR';

    const TYPE_SPACE = ' ';

    /**
     * List of query expressions
     *
     * @var array
     */
    private $expressions = [];

    /**
     * @var string
     */
    private $type;

    /**
     * Create new group of expression
     *
     * @param array $expressions
     * @param string $type
     */
    public function __construct(array $expressions, $type = self::TYPE_SPACE)
    {
        $this->expressions = $expressions;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $parts = [];

        foreach ($this->expressions as $expression) {
            if (!$expression) {
                continue;
            }

            $parts[] = Util::sanitize($expression);
        }

        if (!$parts) {
            return '';
        }

        if ($this->type === static::TYPE_OR || $this->type === static::TYPE_AND) {
            $glue = ' ' . $this->type . ' ';
        } else {
            $glue = $this->type;
        }

        return implode($glue, array_filter($parts));
    }

    /**
     * @param $type
     * @return boolean
     */
    public static function isValidType($type)
    {
        return $type === static::TYPE_OR
            || $type === static::TYPE_AND
            || $type === static::TYPE_SPACE
            || $type === null;
    }
}
