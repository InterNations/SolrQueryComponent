<?php
namespace InterNations\Component\Solr\Expr;

/**
 * Proximity query class
 *
 * Proximity queries allow to search for two words in a specific distance ("<word1> <word2>"~<proximity>)
 */
class ProximityExpression extends Expression
{
    /**
     * Word 1
     *
     * @var string
     */
    protected $wordOne;

    /**
     * Word 2
     *
     * @var string
     */
    protected $wordTwo;

    /**
     * Maximum distance between the two words
     *
     * @var integer
     */
    protected $proximity;

    /**
     * Create new proximity query object
     *
     * @param string $wordOne
     * @param string $wordTwo
     * @param integer $proximity
     */
    public function __construct($wordOne, $wordTwo, $proximity)
    {
        $this->wordOne = $wordOne;
        $this->wordTwo = $wordTwo;
        $this->proximity = (int) $proximity;
    }

    public function __toString()
    {
        return new PhraseExpression($this->wordOne . ' ' . $this->wordTwo) . '~' . $this->proximity;
    }
}
