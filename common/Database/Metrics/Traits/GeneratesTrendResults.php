<?php

namespace Common\Database\Metrics\Traits;

use Carbon\CarbonInterface;
use Common\Database\Metrics\MetricDateRange;

trait GeneratesTrendResults
{
    protected function getAllPossibleDateResults(
        MetricDateRange $dateRange,
    ): array {
        $format = $dateRange->getGroupingFormat();

        // dates in range will already be in correct timezone
        $possibleDateResults = [];
        foreach ($dateRange->period as $date) {
            $possibleDateResults[
                (string) $date->format($format)
            ] = $this->formatTrendResult($dateRange->granularity, $date);
        }

        return $possibleDateResults;
    }

    protected function formatTrendResult(
        string $granularity,
        CarbonInterface $date,
        int $value = 0,
    ): array {
        if ($granularity === $this->dateRange::WEEK) {
            return [
                'date' => $date->startOfWeek()->toISOString(),
                'endDate' => $date->endOfWeek()->toISOString(),
                'value' => $value,
            ];
        } else {
            return [
                'date' => $date->toISOString(),
                'value' => $value,
            ];
        }
    }
}
