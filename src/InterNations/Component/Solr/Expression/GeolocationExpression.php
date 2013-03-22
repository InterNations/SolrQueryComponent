<?php
namespace InterNations\Component\Solr\Expression;


class GeolocationExpression extends Expression
{
    /**
     * @var int
     */
    protected $precision;

    /**
     * @var float
     */
    protected $latitude;

    /**
     * @var float
     */
    protected $longitude;

    /**
     * @param Expression|string $latitude
     * @param $longitude
     * @param int $precision
     */
    public function __construct($latitude, $longitude, $precision)
    {
        $this->precision = (int) $precision;
        $this->latitude = (float) $latitude;
        $this->longitude = (float) $longitude;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            '%.' . $this->precision . 'F,%.' . $this->precision . 'F',
            $this->latitude,
            $this->longitude
        );
    }
}
