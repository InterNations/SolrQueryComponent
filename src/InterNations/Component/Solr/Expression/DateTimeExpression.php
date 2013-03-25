<?php
namespace InterNations\Component\Solr\Expression;

use DateTime;
use DateTimeZone;

class DateTimeExpression extends Expression
{
    /**
     * @var DateTimeZone
     */
    private static $utcTimezone;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var string
     */
    private $format = 'Y-m-d\TH:i:s\Z';

    /**
     * @param DateTime $date
     * @param string $format
     */
    public function __construct(DateTime $date, $format = null)
    {
        $this->date = $date;
        $this->format = $format ? $format : $this->format;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (!self::$utcTimezone) {
            self::$utcTimezone = new DateTimeZone('UTC');
        }

        $date = $this->date->setTimeZone(self::$utcTimezone);

        return $date->format($this->format);
    }
}
