<?php
namespace InterNations\Component\Solr\Expr;

use InterNations\Component\Solr\Util;

/**
 * Class for fuzzy query expressions
 */
class FuzzyExpr extends Expr
{
    /**
     * Similarity (0.0 to 1.0)
     *
     * @var float
     */
    protected $similarity;

    /**
     * Create new fuzzy query object
     *
     * @param string|InterNations\Component\Solr\Expr\Expr $expr
     * @param float $similarity
     */
    public function __construct($expr, $similarity = null)
    {
        parent::__construct($expr);
        if ($similarity !== null) {
            $this->similarity = (float) $similarity;
        }
    }

    public function __toString()
    {
        return Util::escape($this->expr) . '~' . $this->similarity;
    }
}
