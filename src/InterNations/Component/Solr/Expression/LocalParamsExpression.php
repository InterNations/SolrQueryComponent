<?php
namespace InterNations\Component\Solr\Expression;

use InterNations\Component\Solr\Util;

class LocalParamsExpression extends Expression
{
    private $type;

    private $params;

    private $shortForm = true;

    public function __construct($type, array $params = [], $shortForm = true)
    {
        $this->type = $type;
        $this->params = $params;
        $this->shortForm = $shortForm;
    }

    public function __toString()
    {
        $typeString = $this->shortForm ? $this->type : 'type=' . $this->type;

        $params = '';
        if ($this->params) {
            foreach ($this->params as $key => $value) {
                $params .= ' ' . $key . '=' . Util::sanitize($value);
            }
        }

        return '{!' . $typeString .  $params . '}';
    }
}
