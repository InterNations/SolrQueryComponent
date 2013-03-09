<?php
namespace InterNations\Component\Solr\Expr;

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
     * @param Expr|string $expr
     * @return Expr
     */
    public function eq($expr)
    {
        if ($this->ignore($expr)) {
            return null;
        }

        if ($expr instanceof Expr) {
            return $expr;
        }

        return new PhraseExpr($expr);
    }

    /**
     * Create field expression: <field>:<expr>
     *
     * @param string $field
     * @param Expr|string $expr
     * @return Expr
     */
    public function field($field, $expr)
    {
        if ($this->ignore($expr)) {
            return null;
        }

        return new FieldExpr($field, $expr);
    }

    /**
     * Create phrase expression: "term1 term2"
     *
     * @param string $str
     * @return Expr
     */
    public function phrase($str)
    {
        if ($this->ignore($str)) {
            return null;
        }

        return new PhraseExpr($str);
    }

    /**
     * Create boost expression: <expr>^<boost>
     *
     * @param integer $boost
     * @param Expr|string $expr
     * @return Expr
     */
    public function boost($expr, $boost)
    {
        if ($this->ignore($expr) or $this->ignore($boost)) {
            return null;
        }

        return new BoostExpr($boost, $expr);
    }

    /**
     * Create proximity match expression: "<word1> <word2>"~<proximity>
     *
     * @param Expr|string $wordOne
     * @param Expr|string $wordTwo
     * @param integer $proximity
     * @return Expr
     */
    public function prx($wordOne, $wordTwo, $proximity)
    {
        return new ProximityExpr($wordOne, $wordTwo, $proximity);
    }

    /**
     * Create fuzzy expression: <expr>~<similarity>
     *
     * @param Expr|string $expr
     * @param float $similarity Similarity between 0.0 und 1.0
     * @return Expr
     */
    public function fzz($expr, $similarity = null)
    {
        return new FuzzyExpr($expr, $similarity);
    }

    /**
     * Range query expression (inclusive start/end): [start TO end]
     *
     * @param string $start
     * @param string $end
     * @return Expr
     */
    public function range($start = null, $end = null)
    {
        return new RangeExpr($start, $end);
    }

    /**
     * Range query expression (exclusive start/end): {start TO end}
     *
     * @param string $start
     * @param string $end
     * @return Expr
     */
    public function btwnRange($start = null, $end = null)
    {
        return new RangeExpr($start, $end, false);
    }

    /**
     * Create wildcard expression: <prefix>?, <prefix>*, <prefix>?<suffix> or <prefix>*<suffix>
     *
     * @param Expr|string $prefix
     * @param string $wildcard
     * @param Expr|string $suffix
     * @return Expr
     */
    public function wild($prefix, $wildcard = '?', $suffix = null)
    {
        if ($this->ignore($prefix) or $this->ignore($wildcard)) {
            return null;
        }

        return new WildcardExpr($wildcard, $prefix, $suffix);
    }

    /**
     * Create boolean, required expression: +<expr>
     *
     * @param Expr|string $expr
     * @return Expr
     */
    public function req($expr)
    {
        if ($this->ignore($expr)) {
            return null;
        }

        return new BooleanExpr(BooleanExpr::OPERATOR_REQUIRED, $expr);
    }

    /**
     * Create boolean, prohibited expression: -<expr>
     *
     * @param Expr|string $expr
     * @return Expr
     */
    public function prhb($expr)
    {
        if ($this->ignore($expr)) {
            return null;
        }

        return new BooleanExpr(BooleanExpr::OPERATOR_PROHIBITED, $expr);
    }

    /**
     * Create boolean expression
     *
     *      true => required (+)
     *      false => prohibited (-)
     *      null => neutral (<empty>)
     *
     * @param Expr|string $expr
     * @param boolean|null $operator
     * @return Expr
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
     * @param Expr|string $expr
     * @return Expr
     */
    public function lit($expr)
    {
        if ($this->ignore($expr)) {
            return null;
        }

        return new Expr($expr);
    }

    /**
     * Create grouped expression: (<expr1> <expr2> <expr3>)
     *
     * @param Expr|string $expr
     * @param Expr|string $expr
     * @param Expr|string $expr
     * @return Expr
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

        return new GroupExpr($args);
    }

    /**
     * Returns a query "*:*" which means find all if $expr is empty
     *
     * @param Expr|string $expr
     * @return Expr
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
     * @return RangeExpr
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
     * @return RangeExpr
     */
    public function dateRange(DateTime $dateFrom = null, DateTime $dateTo = null)
    {
        $dateFromValue = ExpressionFactory::createExpression($dateFrom);
        $dateToValue = ExpressionFactory::createExpression($dateTo);

        if ($dateFromValue instanceof WildcardExpr && $dateToValue instanceof WildcardExpr) {
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
