<?php

namespace Common\Database\Metrics;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;

class ValueMetric extends BaseMetric
{
    public function __construct(
        Model|Builder|string $model,
        MetricDateRange $dateRange,
        string|Expression|null $column = null,
        ?string $dateColumn = null,
    ) {
        parent::__construct($model, $dateRange, $column, $dateColumn);
    }

    protected function aggregate(string $function): array
    {
        $column =
            $this->column ?: $this->query->getModel()->getQualifiedKeyName();

        $previousValue = round(
            (clone $this->query)
                ->when($this->dateRange, function ($query) {
                    $previous = $this->dateRange->getPreviousPeriod();
                    $query->whereBetween($this->dateColumn, [
                        $previous->start,
                        $previous->end,
                    ]);
                })
                ->{$function}($column) ?? 0,
            $this->roundingPrecision,
            $this->roundingMode,
        );

        $currentValue = round(
            (clone $this->query)
                ->when(
                    $this->dateRange,
                    fn($query) => $query->whereBetween($this->dateColumn, [
                        $this->dateRange->start,
                        $this->dateRange->end,
                    ]),
                )
                ->{$function}($column) ?? 0,
            $this->roundingPrecision,
            $this->roundingMode,
        );

        if (!$currentValue && !$previousValue) {
            $percentageChange = 0; // no change
        } elseif (!$previousValue) {
            $percentageChange = 100; // 100% increase
        } else {
            $percentageChange =
                (($currentValue - $previousValue) / $previousValue) * 100;
        }

        return [
            'previousValue' => $previousValue,
            'currentValue' => $currentValue,
            'percentageChange' => min(300, round($percentageChange, 2)),
        ];
    }
}
