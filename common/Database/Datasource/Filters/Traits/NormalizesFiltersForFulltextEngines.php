<?php

namespace Common\Database\Datasource\Filters\Traits;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait NormalizesFiltersForFulltextEngines
{
    protected function normalizeFilters(array $filters): array
    {
        $normalizedFilters = [];

        foreach ($filters as $index => $filter) {
            // flatten "between" filter
            if ($filter['operator'] === 'between') {
                if ($start = Arr::get($filter, 'value.start')) {
                    $normalizedFilters[] = [
                        'key' => $filter['key'],
                        'operator' => '>=',
                        'value' => Carbon::parse($start)->timestamp,
                    ];
                }
                if ($end = Arr::get($filter, 'value.end')) {
                    $normalizedFilters[] = [
                        'key' => $filter['key'],
                        'operator' => '<=',
                        'value' => Carbon::parse($end)->timestamp,
                    ];
                }
            } else {
                // normalize value and operator, so it's accepted by meilisearch, elastic and algolia
                $normalizedFilters[] = [
                    'key' => $filter['key'],
                    'operator' => $this->normalizeFilterOperator($filter),
                    'value' => $this->normalizeFilterValue($filter),
                ];
            }
        }

        return $normalizedFilters;
    }

    protected function normalizeFilterValue(array $filter): array|string
    {
        $value = $filter['value'];
        if (is_string($value) && Str::contains($value, ' ')) {
            return "'$value'";
        } elseif ($value === null) {
            return '_null';
        } elseif ($value === false) {
            return 'false';
        } elseif ($value === true) {
            return 'true';
        } elseif (
            in_array($filter['key'], $this->query->getModel()->getDates())
        ) {
            return Carbon::parse($value)->timestamp;
        } else {
            return $value;
        }
    }

    protected function normalizeFilterOperator(array $filter): string
    {
        if ($filter['operator'] === 'has') {
            return '=';
        } elseif ($filter['operator'] === 'doesntHave') {
            return '!=';
        } else {
            return $filter['operator'];
        }
    }
}
