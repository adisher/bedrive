<?php

namespace Common\Database\Datasource\Filters;

use Common\Database\Datasource\Filters\Traits\NormalizesFiltersForFulltextEngines;
use Laravel\Scout\Builder;

class MeilisearchFilterer extends BaseFilterer
{
    use NormalizesFiltersForFulltextEngines;

    public function apply(): Builder
    {
        return $this->query
            ->getModel()
            ->search($this->searchTerm, function (
                $driver,
                ?string $query,
                array $options
            ) {
                $filters = $this->prepareFiltersForMeilisearch();
                $filters = implode(' AND ', $filters);
                if ($filters) {
                    $options['filter'] = $filters;
                }
                return $driver->search($query, $options);
            });
    }

    private function prepareFiltersForMeilisearch(): array
    {
        $filters = $this->normalizeFilters($this->filters->getAll());
        return array_map(function ($filter) {
            if (is_array($filter['value'])) {
                $values = array_map(
                    fn($v) => $this->createFilterString(
                        $filter['key'],
                        $filter['operator'],
                        $v,
                    ),
                    $filter['value'],
                );
                return '(' . implode(' OR ', $values) . ')';
            } else {
                return $this->createFilterString(
                    $filter['key'],
                    $filter['operator'],
                    $filter['value'],
                );
            }
        }, $filters);
    }

    private function createFilterString(
        string $key,
        string $operator,
        $value
    ): string {
        return "$key $operator $value";
    }
}
