<?php
namespace InterNations\Component\Solr\Expression;

use InterNations\Component\Solr\Util;

class ParameterExpression extends Expression
{
    /**
     * @var array
     */
    private $parameters = [];

    /** @param mixed[] $parameters */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    public function __toString(): string
    {
        $parameters = array_map([$this, 'replaceNull'], $this->parameters);

        return implode(', ', array_map([Util::class, 'sanitize'], $parameters));
    }

    /**
     * @param mixed $value
     * @return PhraseExpression|mixed
     */
    private function replaceNull($value)
    {
        return $value === null ? new PhraseExpression('') : $value;
    }
}
