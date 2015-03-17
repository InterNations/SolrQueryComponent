<?php
namespace InterNations\Component\Solr\Expression;

class GeofiltExpression extends Expression
{
    /**
     * @var string
     */
    private $field;

    /**
     * @var float
     */
    private $latitude;

    /**
     * @var float
     */
    private $longitude;

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
     * @param array $additionalParams
     */
    public function __construct($field, $latitude, $longitude, $distance, array $additionalParams = array())
    {
        $this->field = (string) $field;
        $this->latitude = (float) $latitude;
        $this->longitude = (float) $longitude;
        $this->distance = (int) $distance;
        $this->additionalParams = $additionalParams;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $params = array('sfield' => $this->field);

        if ($this->latitude && $this->longitude) {
            $params['pt'] = (string) new GeolocationExpression($this->latitude, $this->longitude, 12);
        }

        if ($this->distance) {
            $params['d'] = $this->distance;
        }

        $params = array_merge($params, $this->additionalParams);

        return (string) new LocalParamsExpression('geofilt', $params);
    }
}
