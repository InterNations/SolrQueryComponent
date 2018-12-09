<?php
namespace InterNations\Component\Solr\Expression;

use InterNations\Component\Solr\Util;

class LocalParamsExpression extends Expression
{
    /**
     * @var Expression|string
     */
    private $type;

    /**
     * @var array
     */
    private $params;

    /**
     * @var bool
     */
    private $shortForm = true;

    /**
     * @param Expression|string $type
     * @param mixed[] $params
     */
    public function __construct($type, array $params = [], bool $shortForm = true)
    {
        $this->type = $type;
        $this->params = $params;
        $this->shortForm = $shortForm;
    }

    public function __toString(): string
    {
        $typeString = $this->shortForm ? $this->type : 'type=' . $this->type;
        $paramsString = $this->buildParamString();

        return '{!' . $typeString . $paramsString . '}';
    }

    private function buildParamString(): string
    {
        if ($this->shortForm && count($this->params) === 1 && key($this->params) === $this->type) {
            return '=' . Util::sanitize(current($this->params));
        }

        $paramsString = '';

        foreach ($this->params as $key => $value) {
            $paramsString .= ' ' . $key . '=' . Util::sanitize($value);
        }

        return $paramsString;
    }
}
