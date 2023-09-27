<?php

namespace Common\Database\Metrics;

use Common\Database\Metrics\Traits\RoundingPrecision;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;

abstract class BaseMetric
{
    use RoundingPrecision;

    protected Builder $query;

    public function __construct(
        public Builder|string|Model $model,
        public MetricDateRange $dateRange,
        public string|Expression|null $column = null,
        public string|null $dateColumn = null,
        protected int $limit = 100,
    ) {
        $this->query =
            $this->model instanceof Builder
                ? $this->model->clone()
                : (new $this->model())->newQuery();

        if (!$this->dateColumn) {
            $this->dateColumn = $this->query
                ->getModel()
                ->getQualifiedCreatedAtColumn();
        }
    }

    public function count(): array
    {
        return $this->aggregate('count');
    }

    public function average(): array
    {
        return $this->aggregate('avg');
    }

    public function sum(): array
    {
        return $this->aggregate('sum');
    }

    public function max(): array
    {
        return $this->aggregate('max');
    }

    public function min(): array
    {
        return $this->aggregate('min');
    }

    abstract protected function aggregate(string $function);

    protected function getWrappedColumn(): string
    {
        $column =
            $this->column ?: $this->query->getModel()->getQualifiedKeyName();
        return $column instanceof Expression
            ? (string) $column
            : $this->query
                ->getQuery()
                ->getGrammar()
                ->wrap($column);
    }

    protected function round(int|float $value): float
    {
        return round($value, $this->roundingPrecision, $this->roundingMode);
    }
}
