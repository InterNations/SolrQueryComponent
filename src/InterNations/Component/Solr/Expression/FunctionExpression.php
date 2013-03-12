<?php
namespace InterNations\Component\Solr\Expression;

class FunctionExpression extends Expression
{
    /**
     * @var string
     */
    private $function;

    /**
     * @var array
     */
    private $parameters;

    public function __construct($function, $parameters = null)
    {
        $this->function = $function;
        $this->parameters = $parameters;
    }

    public function __toString()
    {
        $parameters = $this->parameters ?: null;
        if ($parameters && !$parameters instanceof ParameterExpression) {
            $parameters = new ParameterExpression($parameters);
        }

        return $this->function . '(' . $parameters . ')';
    }
}
