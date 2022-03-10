<?php
namespace InterNations\Component\Solr\Expression;

use DateTime;
use DateTimeZone;

class DateTimeExpression extends Expression
{
    public const FORMAT_DEFAULT = 'Y-m-d\TH:i:s\Z';
    public const FORMAT_START_OF_DAY = 'Y-m-d\T00:00:00\Z';
    public const FORMAT_END_OF_DAY = 'Y-m-d\T23:59:59\Z';

    /** @var DateTimeZone */
    private static $utcTimezone;

    /** @var DateTime */
    private $date;

    /** @var string|DateTimeZone */
    private $timezone;

    /** @var string */
    private $format;

    /**
     * @param string|DateTimeZone $timezone
     * @no-named-arguments
     */
    public function __construct(DateTime $date, ?string $format = null, $timezone = 'UTC')
    {
        $this->date = clone $date;
        $this->format = $format ?: static::FORMAT_DEFAULT;
        $this->timezone = $timezone;
    }

    public function __toString(): string
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
