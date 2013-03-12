<?php
namespace InterNations\Component\Solr\Query;

use InterNations\Component\Solr\Expression\DateTimeExpression;
use InterNations\Component\Solr\Expression\GroupExpression;
use InterNations\Component\Solr\Util;
use DateTime;

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

            if ($value instanceof DateTime) {
                $value = new DateTimeExpression($value);
            } elseif (is_array($value)) {
                $value = new GroupExpression($value);
            } else {
                $value = Util::sanitize($value);
            }

            $replacements['<' . $placeholder . '>'] = (string) $value;
        }

        return strtr($this->query, $replacements);
    }
}
