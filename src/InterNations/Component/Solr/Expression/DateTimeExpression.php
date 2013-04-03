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
    private $timezone;

    /**
     * @var string
     */
    private $format = 'Y-m-d\TH:i:s\Z';

    /**
     * @param DateTime $date
     * @param string $format
     * @param string $timezone
     */
    public function __construct(DateTime $date, $format = null, $timezone = 'UTC')
    {
        $this->date = clone $date;
        $this->format = $format ? $format : $this->format;
        $this->timezone = $timezone;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $date = $this->date;
        if ($this->timezone === 'UTC') {
            if (!self::$utcTimezone) {
                self::$utcTimezone = new DateTimeZone('UTC');
            }
            $date = $date->setTimeZone(self::$utcTimezone);
        } elseif ($this->timezone !== null) {
            $date = $date->setTimeZone(new DateTimeZone($this->timezone));
        }

        return $date->format($this->format);
    }
}
