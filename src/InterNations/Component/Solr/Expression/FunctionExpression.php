<?php
namespace InterNations\Component\Solr\Expression;

use InterNations\Component\Solr\ExpressionInterface;

class FunctionExpression extends Expression
{
    /** @var ExpressionInterface|string */
    private $function;

    /** @var ExpressionInterface|array|null */
    private $parameters;

    /**
     * @param ExpressionInterface|string $function
     * @param ExpressionInterface|array|null $parameters
     * @no-named-arguments
     */
    public function __construct($function, $parameters = null)
    {
        $this->function = $function;
        $this->parameters = $parameters;
    }

    public function __toString(): string
    {
        $parameters = $this->parameters ?: null;

        if ($parameters && !$parameters instanceof ParameterExpression) {
            $parameters = new ParameterExpression($parameters);
        }

        return $this->function . '(' . $parameters . ')';
    }
}
