<?php
namespace InterNations\Component\Solr\Expression;

class GeofiltExpression extends Expression
{
    /**
     * @var string
     */
    private $field;

    /**
     * @var GeolocationExpression
     */
    private $geolocation;

    /**
     * @var integer
     */
    private $distance;

    /**
     * @var array
     */
    private $additionalParams;

    /**
     * @param string $field
     * @param GeolocationExpression|null $geolocation
     * @param integer|null $distance
     * @param array $additionalParams
     */
    public function __construct(
        $field,
        GeolocationExpression $geolocation = null,
        $distance = null,
        array $additionalParams = array()
    )
    {
        $this->field = (string) $field;
        $this->geolocation = $geolocation;
        $this->distance = (int) $distance;
        $this->additionalParams = $additionalParams;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $params = array('sfield' => $this->field);

        if ($this->geolocation) {
            $params['pt'] = (string) $this->geolocation;
        }

        if ($this->distance) {
            $params['d'] = $this->distance;
        }

        $params = array_merge($params, $this->additionalParams);

        return (string) new LocalParamsExpression('geofilt', $params);
    }
}
