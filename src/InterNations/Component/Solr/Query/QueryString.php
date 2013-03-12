<?php
namespace InterNations\Component\Solr\Query;

use InterNations\Component\Solr\Util;

class QueryString
{
    /**
     * @var string
     */
    private $query;

    /**
     * @var array
     */
    private $placeholders = [];

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function setPlaceholder($placeholder, $value)
    {
        $this->placeholders[$placeholder] = $value;
    }

    public function setPlaceholders(array $placeholders)
    {
        $this->placeholders = $placeholders;
    }

    public function __toString()
    {
        $replacements = [];
        foreach ($this->placeholders as $placeholder => $value) {
            $replacements['<' . $placeholder . '>'] = Util::sanitize($value);
        }

        return strtr($this->query, $replacements);
    }
}
