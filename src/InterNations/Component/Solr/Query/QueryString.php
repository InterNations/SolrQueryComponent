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

    /**
     * @param string $query
     */
    public function __construct($query)
    {
        $this->query = $query;
    }

    /**
     * Add a value for a placeholder
     *
     * @param string $placeholder
     * @param mixed $value
     * @return QueryString
     */
    public function setPlaceholder($placeholder, $value)
    {
        $this->placeholders[$placeholder] = $value;

        return $this;
    }

    /**
     * Add values for several placeholders as key => value pairs
     *
     * @param array $placeholders
     * @return QueryString
     */
    public function setPlaceholders(array $placeholders)
    {
        $this->placeholders = $placeholders;

        return $this;
    }

    /**
     * Return string representation
     *
     * @return string
     */
    public function __toString()
    {
        $replacements = [];

        foreach ($this->placeholders as $placeholder => $value) {

            if ($value instanceof DateTime) {
                $value = new DateTimeExpression($value);
            } elseif (is_array($value)) {
                $value = new GroupExpression($value);
            } elseif (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } else {
                $value = Util::sanitize($value);
            }

            $replacements['<' . $placeholder . '>'] = (string) $value;
        }

        return strtr($this->query, $replacements);
    }
}
