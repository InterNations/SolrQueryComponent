<?php
namespace InterNations\Component\Solr\Expression;

use DateTime;
use DateTimeZone;
use Functional as F;
use InterNations\Component\Solr\Expression\Exception\InvalidArgumentException;
use InterNations\Component\Solr\ExpressionInterface;

/**
 * @SuppressWarnings(PMD.TooManyMethods)
 * @SuppressWarnings(PMD.CouplingBetweenObjects)
 */
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
            throw InvalidArgumentException::invalidArgument(1, 'timezone', array('string', 'DateTimeZone'), $timezone);
        }

        $this->defaultTimezone = $timezone;
    }

    /**
     * Create term expression: <expr>
     *
     * @param Expression|string $expr
     * @return Expression
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
     *
     * @param string $field
     * @param Expression|string $expr
     * @return FieldExpression
     */
    public function field($field, $expr)
    {
        if ($this->ignore($expr)) {
            return null;
        }

        return new FieldExpression($field, $expr);
    }

    /**
     * Create phrase expression: "term1 term2"
     *
     * @param string $str
     * @return Expression
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
     * @return Expression
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
     * @return Expression
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
     * @param Expression|string $expr
     * @param float $similarity Similarity between 0.0 und 1.0
     * @return Expression
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
     * @param boolean $inclusive
     * @return Expression
     */
    public function range($start = null, $end = null, $inclusive = true)
    {
        return new RangeExpression($start, $end, $inclusive);
    }

    /**
     * Range query expression (exclusive start/end): {start TO end}
     *
     * @param string $start
     * @param string $end
     * @return Expression
     */
    public function btwnRange($start = null, $end = null)
    {
        return new RangeExpression($start, $end, false);
    }

    /**
     * Create wildcard expression: <prefix>?, <prefix>*, <prefix>?<suffix> or <prefix>*<suffix>
     *
     * @param Expression|string $prefix
     * @param string $wildcard
     * @param Expression|string $suffix
     * @return Expression
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
     * @param Expression|string $expr
     * @return Expression
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
     * @param Expression|string $expr
     * @return Expression
     */
    public function prhb($expr)
    {
        if ($this->ignore($expr)) {
            return null;
        }

        return new BooleanExpression(BooleanExpression::OPERATOR_PROHIBITED, $expr);
    }

    /**
     * Create boolean expression
     *
     *      true => required (+)
     *      false => prohibited (-)
     *      null => neutral (<empty>)
     *
     * @param Expression|string $expr
     * @param boolean|null $operator
     * @return Expression
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
     * @param Expression|string $expr
     * @return Expression
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
     * @param Expression|string $expr, ...
     * @param string $type
     * @return Expression
     */
    public function grp($expr = null, $type = null)
    {
        list($args, $type) = $this->parseCompositeArgs(func_get_args());
        if (!$args) {
            return null;
        }

        return new GroupExpression($args, $type);
    }

    /**
     * Returns a query "*:*" which means find all if $expr is empty
     *
     * @param Expression|string $expr
     * @return Expression
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
     * @return RangeExpression
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
     * @return Expression
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
     * @return Expression
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
     * @return Expression
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
     * @param DateTime $to
     * @param boolean $inclusive
     * @param boolean $timezone
     * @return RangeExpression
     */
    public function dateRange(DateTime $from = null, DateTime $to = null, $inclusive = true, $timezone = false)
    {
        if ($from === null && $to === null) {
            return null;
        }

        return $this->range(
            $this->lit($this->date($from, $timezone)),
            $this->lit($this->date($to, $timezone)),
            $inclusive
        );
    }

    /**
     * Create a function expression of name $function
     *
     * You can either pass an array of parameters, a single parameter or a ParameterExpression
     *
     * @param string $function
     * @param array|ParameterExpression|scalar $parameters
     * @return FunctionExpression
     */
    public function func($function, $parameters = null)
    {
        return new FunctionExpression($function, $parameters);
    }

    /**
     * Create a function parameters expression
     *
     * @param array $parameters ,..
     * @return ParameterExpression
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
     * @return LocalParamsExpression
     */
    public function localParams($type, $params = array(), $shortForm = true)
    {
        $additional = null;
        if (!is_bool($shortForm)) {
            $additional = $shortForm;
            $shortForm = true;
        } elseif (!is_array($params)) {
            $additional = $params;
            $params = array();
        }

        return $this->comp(new LocalParamsExpression($type, $params, $shortForm), $additional);
    }

    /**
     * Create composite expression: <expr1> <expr2> <expr3>
     *
     * @param Expression|string $expr, ...
     * @param string $type
     * @return Expression
     */
    public function comp($expr = null, $type = null)
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
     * @return GeolocationExpression
     */
    public function latLong($latitude, $longitude, $precision = 12)
    {
        return new GeolocationExpression($latitude, $longitude, $precision);
    }

    /**
     * @param array $args
     * @return array
     */
    private function parseCompositeArgs(array $args)
    {
        $args = F\flatten($args);
        $type = null;

        if (CompositeExpression::isValidType(end($args))) {
            $type = array_pop($args);
        }

        $args = array_filter($args, array($this, 'permit'));
        if (!$args) {
            return array(false, $type);
        }

        return array($args, $type);
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
