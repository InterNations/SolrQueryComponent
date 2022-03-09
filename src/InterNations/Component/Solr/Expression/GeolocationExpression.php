<?php
namespace InterNations\Component\Solr\Expression;

class GeolocationExpression extends Expression
{
    private $latitude;
    private $longitude;
    private $precision;

    public function __construct(float $latitude, float $longitude, int $precision)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->precision = $precision;
    }

    public function __toString(): string
    {
        return sprintf('%.' . $this->precision . 'F,%.' . $this->precision . 'F', $this->latitude, $this->longitude);
    }
}
