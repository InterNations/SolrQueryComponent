<?php
namespace InterNations\Component\Solr\Expr;

use InterNations\Component\Solr\Util;

/**
 * Wildcard expression class
 *
 * Wildcard expression class is used to generate queries with wildcard expressions in the like of <prefix>*,
 * <prefix>*<suffix>, <prefix>? or <prefix>?<suffix>.
 */
class WildcardExpr extends Expr
{
    /**
     * Wildcard character
     *
     * @var string
     */
    protected $wildcard;

    /**
     * Wildcard query prefix
     *
     * @var string|InterNations\Component\Solr\Expr\Expr
     */
    protected $prefix = '';

    /**
     * Wildcard query suffix
     *
     * @var string|InterNations\Component\Solr\Expr\Expr
     */
    protected $suffix;

    /**
     * Create new wildcard query object
     *
     * @param string $wildcard
     * @param string|InterNations\Component\Solr\Expr\Expr $prefix
     * @param string|InterNations\Component\Solr\Expr\Expr $suffix
     */
    public function __construct($wildcard, $prefix = '', $suffix = null)
    {
        $this->wildcard = $wildcard === '*' ? '*' : '?';
        $this->prefix = $prefix;
        $this->suffix = $suffix;
    }

    /**
     * @inherited
     * @return string
     * @SuppressWarnings(PMD.NPathComplexity)
     * @SuppressWarnings(PMD.CyclomaticComplexity)
     */
    public function  __toString()
    {
        if ($this->prefix instanceof PhraseExpr) {
            $prefix = substr($this->prefix, 0, -1);
            $phrasePrefix = true;
        } else {
            $prefix = Util::escape($this->prefix);
            $phrasePrefix = false;
        }

        if ($this->suffix instanceof PhraseExpr) {
            $suffix = substr($this->suffix, 1);
            $phraseSuffix = true;
        } else {
            $suffix = Util::escape($this->suffix);
            $phraseSuffix = false;
        }

        $expr = (!$phrasePrefix and $phraseSuffix) ? '"' : '';
        $expr .= $prefix;
        $expr .= $this->wildcard;
        $expr .= ($phrasePrefix and !$phraseSuffix and !$suffix) ? '"' : '';
        $expr .= $suffix;
        $expr .= ($phrasePrefix and !$phraseSuffix and $suffix) ? '"' : '';

        return $expr;
    }
}
