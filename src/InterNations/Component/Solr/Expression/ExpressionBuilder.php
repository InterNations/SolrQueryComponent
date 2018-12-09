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
    public function setDefaultTimezone($timezone): void
    {
        if (!is_string($timezone) && !is_object($timezone)) {
            throw InvalidArgumentException::invalidArgument(1, 'timezone', ['string', 'DateTimeZone'], $timezone);
        }

        $this->defaultTimezone = $timezone;
    }

    /**
     * Create term expression: <expr>
     *
     * @param ExpressionInterface|string|null $expr
     */
    public function eq($expr): ?ExpressionInterface
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
     * @param ExpressionInterface|string $field
     * @param ExpressionInterface|string|array $expr
     */
    public function field($field, $expr): ?ExpressionInterface
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
     */
    public function phrase(?string $str): ?ExpressionInterface
    {
        if ($this->ignore($str)) {
            return null;
        }

        return new PhraseExpression($str);
    }

    /**
     * Create boost expression: <expr>^<boost>
     *
     * @param ExpressionInterface|string|null $expr
     */
    public function boost($expr, ?float $boost): ?ExpressionInterface
    {
        if ($this->ignore($expr) or $this->ignore($boost)) {
            return null;
        }

        return new BoostExpression($boost, $expr);
    }

    /**
     * Create proximity match expression: "<word1> <word2>"~<proximity>
     *
     * @param ExpressionInterface|string $word
     * @param int|mixed $proximity
     */
    public function prx($word = null, $proximity = null): ?ExpressionInterface
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
     * @param ExpressionInterface|string|null $expr
     * @param float $similarity Similarity between 0.0 und 1.0
     */
    public function fzz($expr, ?float $similarity = null): ?ExpressionInterface
    {
        if ($this->ignore($expr)) {
            return null;
        }

        return new FuzzyExpression($expr, $similarity);
    }

    /**
     * Range query expression (inclusive start/end): [start TO end]
     *
     * @param string|int|float|ExpressionInterface $start
     * @param string|int|float|ExpressionInterface $end
     */
    public function range($start = null, $end = null, bool $inclusive = true): ExpressionInterface
    {
        return new RangeExpression($start, $end, $inclusive);
    }

    /**
     * Range query expression (exclusive start/end): {start TO end}
     *
     * @param string|int|float|ExpressionInterface $start
     * @param string|int|float|ExpressionInterface $end
     */
    public function btwnRange($start = null, $end = null): ExpressionInterface
    {
        return new RangeExpression($start, $end, false);
    }

    /**
     * Create wildcard expression: <prefix>?, <prefix>*, <prefix>?<suffix> or <prefix>*<suffix>
     *
     * @param ExpressionInterface|string $prefix
     * @param ExpressionInterface|string $suffix
     */
    public function wild($prefix, ?string $wildcard = '?', $suffix = null): ?ExpressionInterface
    {
        if (($this->ignore($prefix) && $this->ignore($suffix)) || $this->ignore($wildcard)) {
            return null;
        }

        return new WildcardExpression($wildcard, $prefix, $suffix);
    }

    /**
     * Create bool, required expression: +<expr>
     *
     * @param ExpressionInterface|string|null $expr
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
     * Create bool, prohibited expression: -<expr>
     *
     * @param ExpressionInterface|string|null $expr
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
     * Create bool, prohibited expression using the NOT notation, usable in OR/AND expressions:
     * (*:* NOT <expr>), e.g. (*:* NOT fieldName:*)
     *
     * @param ExpressionInterface|string|null $expr
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
     * Create bool expression
     *
     *      true => required (+)
     *      false => prohibited (-)
     *      null => neutral (<empty>)
     *
     * @param ExpressionInterface|string|null $expr
     * @param bool|null $operator @codingStandardsIgnoreLine
     * @return ExpressionInterface|null
     */
    public function bool($expr, $operator) // @codingStandardsIgnoreLine

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
     * @param ExpressionInterface|string|null $expr
     */
    public function lit($expr): ?ExpressionInterface
    {
        if ($this->ignore($expr)) {
            return null;
        }

        return new Expression($expr);
    }

    /**
     * Create grouped expression: (<expr1> <expr2> <expr3>)
     *
     * @param ExpressionInterface|string|null $expr
     * @param string|mixed $type
     */
    public function grp($expr = null, $type = CompositeExpression::TYPE_SPACE): ?ExpressionInterface
    {
        [$args, $type] = $this->parseCompositeArgs(func_get_args());

        if (!$args) {
            return null;
        }

        return new GroupExpression($args, $type);
    }

    /**
     * Create AND grouped expression: (<expr1> AND <expr2> AND <expr3>)
     *
     * @param ExpressionInterface[]|string[] $args
     */
    public function andX(...$args): ?ExpressionInterface
    {
        $args = $this->parseCompositeArgs($args)[0];

        if (!$args) {
            return null;
        }

        return new GroupExpression($args, GroupExpression::TYPE_AND);
    }

    /**
     * Create OR grouped expression: (<expr1> OR <expr2> OR <expr3>)
     *
     * @param ExpressionInterface[]|string[] $args
     */
    public function orX(...$args): ?ExpressionInterface
    {
        $args = $this->parseCompositeArgs($args)[0];

        if (!$args) {
            return null;
        }

        return new GroupExpression($args, GroupExpression::TYPE_OR);
    }

    /**
     * Returns a query "*:*" which means find all if $expr is empty
     *
     * @param ExpressionInterface|string|null $expr
     * @return ExpressionInterface|mixed
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
     * @param DateTime|mixed $date
     */
    public function day($date = null): ?ExpressionInterface
    {
        if (!$date instanceof DateTime) {
            return null;
        }

        return $this->range($this->startOfDay($date), $this->endOfDay($date));
    }

    /**
     * Expression for the start of the given date
     *
     * @param bool|string $timezone
     */
    public function startOfDay(?DateTime $date = null, $timezone = false): ?ExpressionInterface
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
     * @param bool|string $timezone
     */
    public function endOfDay(?DateTime $date = null, $timezone = false): ?ExpressionInterface
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

    /** @param bool|string $timezone */
    public function date(?DateTime $date = null, $timezone = false): ExpressionInterface
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
     * @param bool|string $timezone
     */
    public function dateRange(
        ?DateTime $from = null,
        ?DateTime $to = null,
        bool $inclusive = true,
        $timezone = false
    ): ?ExpressionInterface
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
     * @param array|ParameterExpressionInterface|string|null $parameters
     */
    public function func(string $function, $parameters = null): ExpressionInterface
    {
        return new FunctionExpression($function, $parameters);
    }

    /**
     * Create a function parameters expression
     *
     * @param mixed $parameters
     */
    public function params(...$parameters): ExpressionInterface
    {
        $parameters = F\flatten($parameters);

        return new ParameterExpression($parameters);
    }

    /**
     * @param mixed[]|mixed $params
     * @param bool|mixed $shortForm
     */
    public function localParams(string $type, $params = [], $shortForm = true): ?ExpressionInterface
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

    /** @param mixed[] $additionalParams */
    public function geofilt(
        string $field,
        ?GeolocationExpression $geolocation = null,
        ?int $distance = null,
        array $additionalParams = []
    ): ExpressionInterface
    {
        return new GeofiltExpression($field, $geolocation, $distance, $additionalParams);
    }

    /**
     * Create composite expression: <expr1> <expr2> <expr3>
     *
     * @param ExpressionInterface|string|null $expr
     */
    public function comp($expr = null, ?string $type = CompositeExpression::TYPE_SPACE): ?ExpressionInterface
    {
        [$args, $type] = $this->parseCompositeArgs(func_get_args());

        if (!$args) {
            return null;
        }

        return new CompositeExpression($args, $type);
    }

    /**
     * Create a geo location expression: "<latitude>,<longitude>" using the given precision
     */
    public function latLong(float $latitude, float $longitude, int $precision = 12): ExpressionInterface
    {
        return new GeolocationExpression($latitude, $longitude, $precision);
    }

    /** @param string|ExpressionInterface|null $expr */
    public function noCache($expr = null): ?ExpressionInterface
    {
        if ($this->ignore($expr)) {
            return null;
        }

        return $this->comp([$this->shortLocalParams('cache', false), $expr], null);
    }

    /** @param string|ExpressionInterface|null $expr */
    public function tag(string $tagName, $expr = null): ?ExpressionInterface
    {
        if ($this->ignore($expr)) {
            return null;
        }

        return $this->comp([$this->shortLocalParams('tag', $tagName), $expr], null);
    }

    /** @param string|ExpressionInterface|null $expr */
    public function excludeTag(string $tagName, $expr = null): ?ExpressionInterface
    {
        if ($this->ignore($expr)) {
            return null;
        }

        return $this->comp([$this->shortLocalParams('ex', $tagName), $expr], null);
    }

    /**
     * @param ExpressionInterface|string $tag
     * @param mixed $value
     */
    private function shortLocalParams($tag, $value): LocalParamsExpression
    {
        return new LocalParamsExpression($tag, [$tag => $value], true);
    }

    /**
     * @param mixed[] $args
     * @return mixed[]
     */
    private function parseCompositeArgs(array $args): array
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

    /** @param mixed $expr */
    private function ignore($expr): bool
    {
        return $expr === null || (is_string($expr) && trim($expr) === '');
    }

    /** @param mixed $expr */
    private function permit($expr): bool
    {
        return !$this->ignore($expr);
    }
}
