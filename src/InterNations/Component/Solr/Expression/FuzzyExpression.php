<?php
namespace InterNations\Component\Solr\Expression;

use InterNations\Component\Solr\ExpressionInterface;
use InterNations\Component\Solr\Util;

/**
 * Class for fuzzy query expressions
 */
class FuzzyExpression extends Expression
{
    /**
     * Similarity (0.0 to 1.0)
     *
     * @var float
     */
    private $similarity;

    /**
     * Create new fuzzy query object
     *
     * @param ExpressionInterface|string $expr
	 * @no-named-arguments
     */
    public function __construct($expr, ?float $similarity = null)
    {
        parent::__construct($expr);

        if ($similarity !== null) {
            $this->similarity = $similarity;
        }
    }

    public function __toString(): string
    {
        return Util::escape($this->expr) . '~' . $this->similarity;
    }
}
