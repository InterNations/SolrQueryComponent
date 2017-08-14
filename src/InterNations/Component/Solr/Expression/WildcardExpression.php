<?php
namespace InterNations\Component\Solr\Expression;

use InterNations\Component\Solr\Util;

/**
 * Wildcard expression class
 *
 * Wildcard expression class is used to generate queries with wildcard expressions in the like of <prefix>*,
 * <prefix>*<suffix>, <prefix>? or <prefix>?<suffix>.
 */
class WildcardExpression extends Expression
{
    /**
     * Wildcard character
     *
     * @var string
     */
    private $wildcard;

    /**
     * Wildcard query prefix
     *
     * @var string|Expression
     */
    private $prefix = '';

    /**
     * Wildcard query suffix
     *
     * @var string|Expression
     */
    private $suffix;

    /**
     * Create new wildcard query object
     *
     * @param string|Expression $prefix
     * @param string|Expression $suffix
     */
    public function __construct(string $wildcard, $prefix = '', $suffix = null)
    {
        $this->wildcard = $wildcard === '*' ? '*' : '?';
        $this->prefix = $prefix;
        $this->suffix = $suffix;
    }

    public function  __toString(): string
    {
        if ($this->prefix instanceof PhraseExpression) {
            $prefix = substr($this->prefix, 0, -1);
            $phrasePrefix = true;
        } else {
            $prefix = Util::escape($this->prefix);
            $phrasePrefix = false;
        }

        if ($this->suffix instanceof PhraseExpression) {
            $suffix = substr($this->suffix, 1);
            $phraseSuffix = true;
        } else {
            $suffix = Util::escape($this->suffix);
            $phraseSuffix = false;
        }

        $expr = (!$phrasePrefix && $phraseSuffix) ? '"' : '';
        $expr .= $prefix;
        $expr .= $this->wildcard;
        $expr .= ($phrasePrefix && !$phraseSuffix && !$suffix) ? '"' : '';
        $expr .= $suffix;
        $expr .= ($phrasePrefix && !$phraseSuffix && $suffix) ? '"' : '';

        return $expr;
    }
}
