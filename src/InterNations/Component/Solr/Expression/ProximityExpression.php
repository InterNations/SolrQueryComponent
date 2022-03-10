<?php
namespace InterNations\Component\Solr\Expression;

use InterNations\Component\Solr\Util;

/**
 * Proximity query class
 *
 * Proximity queries allow to search for two words in a specific distance ("<word1> <word2>"~<proximity>)
 */
class ProximityExpression extends Expression
{
    /** @var array */
    private $words;

    /**
     * Maximum distance between the two words
     *
     * @var int
     */
    private $proximity;

    /**
     * Create new proximity query object
     *
     * @param string[] $words
	 * @no-named-arguments
     */
    public function __construct(array $words, int $proximity)
    {
        $this->words = $words;
        $this->proximity = $proximity;
    }

    public function __toString(): string
    {
        return Util::quote(implode(' ', $this->words)) . '~' . $this->proximity;
    }
}
