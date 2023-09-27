<?php

namespace Common\Database\Metrics;

use Common\Database\Metrics\Traits\GeneratesTrendResults;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Trend extends BaseMetric
{
    use GeneratesTrendResults;

    protected function aggregate(string $function): array
    {
        $expression = new TrendDateExpression(
            $this->query,
            $this->dateColumn,
            $this->dateRange->granularity,
            $this->dateRange->timezone,
        );

        $results = $this->query
            ->select(
                DB::raw(
                    "{$expression} as date_result, {$function}({$this->getWrappedColumn()}) as aggregate",
                ),
            )
            ->whereBetween($this->dateColumn, [
                $this->dateRange->start,
                $this->dateRange->end,
            ])
            ->groupBy(DB::raw($expression))
            ->orderBy('date_result')
            ->limit($this->limit)
            ->get();

        $mergedResults = array_replace(
            $this->getAllPossibleDateResults($this->dateRange),
            $results
                ->mapWithKeys(function ($result) {
                    return [
                        (string) $result->date_result => $this->formatTrendResult(
                            $this->dateRange->granularity,
                            $this->parseMysqlDate($result->date_result),
                            $result->aggregate,
                        ),
                    ];
                })
                ->all(),
        );

        return array_values($mergedResults);
    }

    protected function parseMysqlDate(string $mysqlDate): Carbon
    {
        // dates coming from mysql will be in user's timezone (due to + Interval x HOUR),
        // set user's timezone on carbon as well, so that "toIsoString" will return correct date.
        if ($this->dateRange->granularity === $this->dateRange::WEEK) {
            [$year, $week] = explode('-', $mysqlDate);
            return Carbon::today($this->dateRange->timezone)->setISODate(
                (int) $year,
                (int) $week,
            );
        } else {
            return Carbon::parse($mysqlDate, $this->dateRange->timezone);
        }
    }
}
