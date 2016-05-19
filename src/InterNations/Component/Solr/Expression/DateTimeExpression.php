<?php
namespace InterNations\Component\Solr\Expression;

use DateTime;
use DateTimeZone;

class DateTimeExpression extends Expression
{
    const FORMAT_DEFAULT = 'Y-m-d\TH:i:s\Z';

    const FORMAT_START_OF_DAY = 'Y-m-d\T00:00:00\Z';

    const FORMAT_END_OF_DAY = 'Y-m-d\T23:59:59\Z';

    /**
     * @var DateTimeZone
     */
    private static $utcTimezone;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var string|DateTimeZone
     */
    private $timezone;

    /**
     * @var string
     */
    private $format;

    /**
     * @param DateTime $date
     * @param string $format
     * @param string|DateTimeZone $timezone
     */
    public function __construct(DateTime $date, $format = null, $timezone = 'UTC')
    {
        $this->date = clone $date;
        $this->format = $format ?: static::FORMAT_DEFAULT;
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
            if ($this->timezone instanceof DateTimeZone) {
                $date = $date->setTimeZone($this->timezone);
            } else {
                $date = $date->setTimeZone(new DateTimeZone($this->timezone));
            }
        }

        return $date->format($this->format);
    }
}
