<?php
namespace InterNations\Component\Solr\Query;

use InterNations\Component\Solr\Expression\DateTimeExpression;
use InterNations\Component\Solr\Expression\GroupExpression;
use InterNations\Component\Solr\Util;
use DateTime;

class QueryString
{
    /** @var string */
    private $query;

    /** @var array */
    private $placeholders = [];

    /** @no-named-arguments */
    public function __construct(string $query)
    {
        $this->query = $query;
    }

    /**
     * Add a value for a placeholder
     *
     * @param mixed $value
     * @no-named-arguments
     */
    public function setPlaceholder(string $placeholder, $value): self
    {
        $this->placeholders[$placeholder] = $value;

        return $this;
    }

    /**
     * Add values for several placeholders as key => value pairs
     *
     * @param mixed[] $placeholders
     * @no-named-arguments
     */
    public function setPlaceholders(array $placeholders): self
    {
        $this->placeholders = $placeholders;

        return $this;
    }

    /** Return string representation */
    public function __toString(): string
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
