<?php
namespace InterNations\Component\Solr\Expression;

use DateTime;
use DateTimeZone;

/**
 * @SuppressWarnings(PMD.TooManyMethods)
 * @SuppressWarnings(PMD.CouplingBetweenObjects)
 */
class ExpressionBuilder
{
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

        if ($expr instanceof Expression) {
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
     * @param Expression|string $wordOne
     * @param Expression|string $wordTwo
     * @param integer $proximity
     * @return Expression
     */
    public function prx($wordOne, $wordTwo, $proximity)
    {
        return new ProximityExpression($wordOne, $wordTwo, $proximity);
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
     * @return Expression
     */
    public function range($start = null, $end = null)
    {
        return new RangeExpression($start, $end);
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
        if ($this->ignore($prefix) or $this->ignore($wildcard)) {
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
     * @param Expression|string $expr
     * @param Expression|string $expr
     * @param Expression|string $expr
     * @return Expression
     */
    public function grp()
    {
        $args = func_get_args();

        if (func_num_args() === 1 && is_array(func_get_arg(0))) {
            $args = func_get_arg(0);
        }

        $args = array_filter($args, [$this, 'permit']);

        if (!$args) {
            return null;
        }

        return new GroupExpression($args);
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
     * Creates a date range for a specific day
     *
     * @param DateTime $date
     * @return RangeExpression
     */
    public function day($date = null)
    {
        if (!$date instanceof DateTime) {
            return null;
        }

        $date = clone $date;
        $date->setTimezone(new DateTimeZone('UTC'));

        $expr = $this->range(
            $this->lit($date->format('Y-m-d\T00:00:00\Z')),
            $this->lit($date->format('Y-m-d\T23:59:59\Z'))
        );

        return $expr;
    }

    /**
     * Creates a range between to dates (one side may be open ended which is indicated by passing null)
     *
     * @param DateTime $dateFrom
     * @param DateTime $dateTo
     * @return RangeExpression
     */
    public function dateRange(DateTime $dateFrom = null, DateTime $dateTo = null)
    {
        $dateFromValue = ExpressionFactory::createExpression($dateFrom);
        $dateToValue = ExpressionFactory::createExpression($dateTo);

        if ($dateFromValue instanceof WildcardExpression && $dateToValue instanceof WildcardExpression) {
            return null;
        }

        $expr = $this->range(
            $this->lit($dateFromValue),
            $this->lit($dateToValue)
        );

        return $expr;
    }

    private function ignore($expr)
    {
        return trim($expr) === '';
    }

    private function permit($expr)
    {
        return !$this->ignore($expr);
    }
}
