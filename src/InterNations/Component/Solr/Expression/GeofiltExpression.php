<?php
namespace InterNations\Component\Solr\Expression;

class GeofiltExpression extends Expression
{
    private $field;
    private $geolocation;
    private $distance;
    private $additionalParams;

    /**
     * @param mixed[] $additionalParams
     */
    public function __construct(
        string $field,
        ?GeolocationExpression $geolocation = null,
        ?int $distance = null,
        array $additionalParams = []
    )
    {
        $this->field = $field;
        $this->geolocation = $geolocation;
        $this->distance = (int) $distance;
        $this->additionalParams = $additionalParams;
    }

    public function __toString(): string
    {
        $params = ['sfield' => $this->field];

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
