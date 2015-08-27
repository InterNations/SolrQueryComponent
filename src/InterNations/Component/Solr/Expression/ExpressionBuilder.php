<?php
namespace InterNations\Component\Solr\Expression;

use DateTime;
use DateTimeZone;
use Functional as F;
use InterNations\Component\Solr\Expression\Exception\InvalidArgumentException;
use InterNations\Component\Solr\ExpressionInterface;

class ExpressionBuilder
{
    /**
     * @var string|DateTimeZone
     */
    private $defaultTimezone = 'UTC';

    /**
     * Set default timezone for the Solr search server
     *
     * The default timezone is used to convert date queries. You can either
     * pass a string (like "Europe/Berlin") or a DateTimeZone object.
     *
     * @param DateTimeZone|string $timezone
     * @throws InvalidArgumentException
     */
    public function setDefaultTimezone($timezone)
    {
        if (!is_string($timezone) && !is_object($timezone)) {
            throw InvalidArgumentException::invalidArgument(1, 'timezone', ['string', 'DateTimeZone'], $timezone);
        }

        $this->defaultTimezone = $timezone;
    }

    /**
     * Create term expression: <expr>
     *
     * @param ExpressionInterface|string $expr
     * @return ExpressionInterface|null
     */
    public function eq($expr)
    {
        if ($this->ignore($expr)) {
            return null;
        }

        if ($expr instanceof ExpressionInterface) {
            return $expr;
        }

        return new PhraseExpression($expr);
    }

    /**
     * Create field expression: <field>:<expr>
     * of in an array $expr is given: <field>:(<expr1> <expr2> <expr3>...)
     *
     * @param string $field
     * @param ExpressionInterface|string|array $expr
     * @return ExpressionInterface|null
     */
    public function field($field, $expr)
    {
        if (is_array($expr)) {
            $expr = $this->grp($expr);
        } elseif ($this->ignore($expr)) {
            return null;
        }

        return new FieldExpression($field, $expr);
    }

    /**
     * Create phrase expression: "term1 term2"
     *
     * @param string $str
     * @return ExpressionInterface|null
     */
    public function phrase($str)
    {
        if ($this->ignore($str)) {
            return null;
        }

        return new PhraseExpression($str);
    }

    /**
     * Create boost expression: <expr>^<boost>
     *
     * @param integer $boost
     * @param Expression|string $expr
     * @return ExpressionInterface|null
     */
    public function boost($expr, $boost)
    {
        if ($this->ignore($expr) or $this->ignore($boost)) {
            return null;
        }

        return new BoostExpression($boost, $expr);
    }

    /**
     * Create proximity match expression: "<word1> <word2>"~<proximity>
     *
     * @param Expression|string $word, ...
     * @param integer $proximity
     * @return ExpressionInterface|null
     */
    public function prx($word = null, $proximity = null)
    {
        $arguments = func_get_args();
        $proximity = array_pop($arguments);

        $arguments = F\flatten($arguments);

        if (!$arguments) {
            return null;
        }

        return new ProximityExpression($arguments, $proximity);
    }

    /**
     * Create fuzzy expression: <expr>~<similarity>
     *
     * @param ExpressionInterface|string $expr
     * @param float $similarity Similarity between 0.0 und 1.0
     * @return ExpressionInterface
     */
    public function fzz($expr, $similarity = null)
    {
        return new FuzzyExpression($expr, $similarity);
    }

    /**
     * Range query expression (inclusive start/end): [start TO end]
     *
     * @param string $start
     * @param string $end
     * @param boolean $inclusiveFrom
     * @param boolean $inclusiveTo
     * @return ExpressionInterface
     */
    public function range($start = null, $end = null, $inclusiveFrom = true, $inclusiveTo = true)
    {
        return new RangeExpression($start, $end, $inclusiveFrom, $inclusiveTo);
    }

    /**
     * Range query expression (exclusive start/end): {start TO end}
     *
     * @param string $start
     * @param string $end
     * @return ExpressionInterface
     */
    public function btwnRange($start = null, $end = null)
    {
        return new RangeExpression($start, $end, false, false);
    }

    /**
     * Create wildcard expression: <prefix>?, <prefix>*, <prefix>?<suffix> or <prefix>*<suffix>
     *
     * @param ExpressionInterface|string $prefix
     * @param string $wildcard
     * @param ExpressionInterface|string $suffix
     * @return ExpressionInterface|null
     */
    public function wild($prefix, $wildcard = '?', $suffix = null)
    {
        if (($this->ignore($prefix) && $this->ignore($suffix)) or $this->ignore($wildcard)) {
            return null;
        }

        return new WildcardExpression($wildcard, $prefix, $suffix);
    }

    /**
     * Create boolean, required expression: +<expr>
     *
     * @param ExpressionInterface|string $expr
     * @return ExpressionInterface|null
     */
    public function req($expr)
    {
        if ($this->ignore($expr)) {
            return null;
        }

        return new BooleanExpression(BooleanExpression::OPERATOR_REQUIRED, $expr);
    }

    /**
     * Create boolean, prohibited expression: -<expr>
     *
     * @param ExpressionInterface|string $expr
     * @return ExpressionInterface|null
     */
    public function prhb($expr)
    {
        if ($this->ignore($expr)) {
            return null;
        }

        return new BooleanExpression(BooleanExpression::OPERATOR_PROHIBITED, $expr);
    }

    /**
     * Create boolean, prohibited expression using the NOT notation, usable in OR/AND expressions:
     * (*:* NOT <expr>), e.g. (*:* NOT fieldName:*)
     *
     * @param ExpressionInterface|string $expr
     * @return ExpressionInterface|null
     */
    public function not($expr)
    {
        if ($this->ignore($expr)) {
            return null;
        }

        return new BooleanExpression(BooleanExpression::OPERATOR_PROHIBITED, $expr, true);
    }

    /**
     * Create boolean expression
     *
     *      true => required (+)
     *      false => prohibited (-)
     *      null => neutral (<empty>)
     *
     * @param ExpressionInterface|string $expr
     * @param boolean|null $operator
     * @return ExpressionInterface|null
     */
    public function bool($expr, $operator)
    {
        if ($operator === null) {
            return $expr;
        }

        if ($operator) {
            return $this->req($expr);
        } else {
            return $this->prhb($expr);
        }
    }

    /**
     * Return string treated as literal (unescaped, unquoted)
     *
     * @param ExpressionInterface|string $expr
     * @return ExpressionInterface|null
     */
    public function lit($expr)
    {
        if ($this->ignore($expr)) {
            return null;
        }

        return new Expression($expr);
    }

    /**
     * Create grouped expression: (<expr1> <expr2> <expr3>)
     *
     * @param ExpressionInterface|string $expr, ...
     * @param string $type
     * @return ExpressionInterface|null
     */
    public function grp($expr = null, $type = CompositeExpression::TYPE_SPACE)
    {
        list($args, $type) = $this->parseCompositeArgs(func_get_args());
        if (!$args) {
            return null;
        }

        return new GroupExpression($args, $type);
    }

    /**
     * Create AND grouped expression: (<expr1> AND <expr2> AND <expr3>)
     *
     * @param ExpressionInterface|string $expr, ...
     * @param ExpressionInterface|string $expr
     * @return ExpressionInterface|null
     */
    public function andX($expr = null)
    {
        $args = $this->parseCompositeArgs(func_get_args())[0];
        if (!$args) {
            return null;
        }

        return new GroupExpression($args, GroupExpression::TYPE_AND);
    }

    /**
     * Create OR grouped expression: (<expr1> OR <expr2> OR <expr3>)
     *
     * @param ExpressionInterface|string $expr , ...
     * @return ExpressionInterface|null
     */
    public function orX($expr = null)
    {
        $args = $this->parseCompositeArgs(func_get_args())[0];
        if (!$args) {
            return null;
        }

        return new GroupExpression($args, GroupExpression::TYPE_OR);
    }

    /**
     * Returns a query "*:*" which means find all if $expr is empty
     *
     * @param ExpressionInterface|string $expr
     * @return ExpressionInterface|null
     */
    public function all($expr = null)
    {
        if ($this->permit($expr)) {
            return $expr;
        }

        return $this->field($this->lit('*'), $this->lit('*'));
    }

    /**
     * Create a date expression for a specific day
     *
     * @param DateTime $date
     * @return ExpressionInterface|null
     */
    public function day($date = null)
    {
        if (!$date instanceof DateTime) {
            return null;
        }

        return $this->range($this->startOfDay($date), $this->endOfDay($date));
    }

    /**
     * Expression for the start of the given date
     *
     * @param DateTime|null $date
     * @param boolean|string $timezone
     * @return ExpressionInterface|null
     */
    public function startOfDay(DateTime $date = null, $timezone = false)
    {
        if ($date === null) {
            return null;
        }

        return new DateTimeExpression(
            $date,
            DateTimeExpression::FORMAT_START_OF_DAY,
            $timezone === false ? $this->defaultTimezone : $timezone
        );
    }

    /**
     * Expression for the end of the given date
     *
     * @param DateTime|null $date
     * @param boolean|string $timezone
     * @return ExpressionInterface|null
     */
    public function endOfDay(DateTime $date = null, $timezone = false)
    {
        if (!$date) {
            return null;
        }

        return new DateTimeExpression(
            $date,
            DateTimeExpression::FORMAT_END_OF_DAY,
            $timezone === false ? $this->defaultTimezone : $timezone
        );
    }

    /**
     * @param DateTime $date
     * @param boolean|string $timezone
     * @return ExpressionInterface
     */
    public function date(DateTime $date = null, $timezone = false)
    {
        if ($date === null) {
            return new WildcardExpression('*');
        }

        return new DateTimeExpression(
            $date,
            DateTimeExpression::FORMAT_DEFAULT,
            $timezone === false ? $this->defaultTimezone : $timezone
        );
    }

    /**
     * Create a range between two dates (one side may be unlimited which is indicated by passing null)
     *
     * @param DateTime $from
     * @param boolean $inclusiveFrom
     * @param DateTime $to
     * @param boolean $inclusiveTo
     * @param boolean $timezone
     * @return ExpressionInterface|null
     */
    public function dateRange(DateTime $from = null,
        DateTime $to = null,
        $inclusiveFrom = true,
        $inclusiveTo = true,
        $timezone = false)
    {
        if ($from === null && $to === null) {
            return null;
        }

        return $this->range(
            $this->lit($this->date($from, $timezone)),
            $this->lit($this->date($to, $timezone)),
            $inclusiveFrom,
            $inclusiveTo
        );
    }

    /**
     * Create a function expression of name $function
     *
     * You can either pass an array of parameters, a single parameter or a ParameterExpression
     *
     * @param string $function
     * @param array|ParameterExpression|string $parameters
     * @return ExpressionInterface
     */
    public function func($function, $parameters = null)
    {
        return new FunctionExpression($function, $parameters);
    }

    /**
     * Create a function parameters expression
     *
     * @param array $parameters ,..
     * @return ExpressionInterface
     */
    public function params($parameters = null)
    {
        $parameters = F\flatten(func_get_args());

        return new ParameterExpression($parameters);
    }

    /**
     * @param string $type
     * @param array $params
     * @param boolean $shortForm
     * @return ExpressionInterface|null
     */
    public function localParams($type, $params = [], $shortForm = true)
    {
        $additional = null;
        if (!is_bool($shortForm)) {
            $additional = $shortForm;
            $shortForm = true;
        } elseif (!is_array($params)) {
            $additional = $params;
            $params = [];
        }

        if ($additional !== null) {
            return $this->comp(new LocalParamsExpression($type, $params, $shortForm), $additional);
        }

        return new LocalParamsExpression($type, $params, $shortForm);
    }

    /**
     * @param string $field
     * @param GeolocationExpression|null $geolocation
     * @param integer|null $distance
     * @param array $additionalParams
     * @return ExpressionInterface
     */
    public function geofilt(
        $field,
        GeolocationExpression $geolocation = null,
        $distance = null,
        $additionalParams = []
    )
    {
        return new GeofiltExpression($field, $geolocation, $distance, $additionalParams);
    }

    /**
     * Create composite expression: <expr1> <expr2> <expr3>
     *
     * @param ExpressionInterface|string $expr, ...
     * @param string $type
     * @return ExpressionInterface|null
     */
    public function comp($expr = null, $type = CompositeExpression::TYPE_SPACE)
    {
        list($args, $type) = $this->parseCompositeArgs(func_get_args());
        if (!$args) {
            return null;
        }

        return new CompositeExpression($args, $type);
    }

    /**
     * Create a geo location expression: "<latitude>,<longitude>" using the given precision
     *
     * @param float $latitude
     * @param float $longitude
     * @param integer $precision
     * @return ExpressionInterface
     */
    public function latLong($latitude, $longitude, $precision = 12)
    {
        return new GeolocationExpression($latitude, $longitude, $precision);
    }

    /**
     * @param ExpressionInterface|string $expr
     * @return ExpressionInterface|null
     */
    public function noCache($expr = null)
    {
        if ($this->ignore($expr)) {
            return null;
        }

        return $this->comp([$this->shortLocalParams('cache', false), $expr], null);
    }

    /**
     * @param string $tagName
     * @param ExpressionInterface|null $expr
     * @return ExpressionInterface|null
     */
    public function tag($tagName, $expr = null)
    {
        if ($this->ignore($expr)) {
            return null;
        }

        return $this->comp([$this->shortLocalParams('tag', $tagName), $expr], null);
    }

    /**
     * @param string $tagName
     * @param ExpressionInterface|null $expr
     * @return CompositeExpression|null
     */
    public function excludeTag($tagName, $expr = null)
    {
        if ($this->ignore($expr)) {
            return null;
        }

        return $this->comp([$this->shortLocalParams('ex', $tagName), $expr], null);
    }

    private function shortLocalParams($tag, $value)
    {
        return new LocalParamsExpression($tag, [$tag => $value], true);
    }

    /**
     * @param array $args
     * @return array
     */
    private function parseCompositeArgs(array $args)
    {
        $args = F\flatten($args);
        $type = CompositeExpression::TYPE_SPACE;

        if (CompositeExpression::isValidType(end($args))) {
            $type = array_pop($args);
        }

        $args = array_filter($args, [$this, 'permit']);
        if (!$args) {
            return [false, $type];
        }

        return [$args, $type];
    }

    /**
     * @param mixed $expr
     * @return boolean
     */
    private function ignore($expr)
    {
        return trim($expr) === '';
    }

    /**
     * @param mixed $expr
     * @return boolean
     */
    private function permit($expr)
    {
        return !$this->ignore($expr);
    }
}
