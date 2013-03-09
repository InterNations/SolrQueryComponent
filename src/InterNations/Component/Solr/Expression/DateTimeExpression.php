<?php
namespace InterNations\Component\Solr\Expression;

use DateTime;
use DateTimeZone;
use InterNations\Component\Solr\Util;

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

    public function __construct(DateTime $date, $format = null)
    {
        $this->date = $date;
        $this->format = $format ? $format : $this->format;
    }

    public function __toString()
    {
        if (!self::$utcTimezone) {
            self::$utcTimezone = new DateTimeZone('UTC');
        }

        $date = $this->date->setTimeZone(self::$utcTimezone);

        return $date->format($this->format);
    }
}
