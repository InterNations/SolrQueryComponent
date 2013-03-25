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
    /**
     * @var array
     */
    private $words = [];

    /**
     * Maximum distance between the two words
     *
     * @var integer
     */
    protected $proximity;

    /**
     * Create new proximity query object
     *
     * @param array $words
     * @param integer $proximity
     */
    public function __construct(array $words, $proximity)
    {
        $this->words = $words;
        $this->proximity = (int) $proximity;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return Util::quote(join(' ', $this->words)) . '~' . $this->proximity;
    }
}
