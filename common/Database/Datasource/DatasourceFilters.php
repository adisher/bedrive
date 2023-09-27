<?php

namespace Common\Database\Datasource;

use Auth;
use Illuminate\Support\Collection;

class DatasourceFilters
{
    private array $filters;

    public function __construct(?string $encodedFilters = '')
    {
        $this->filters = $this->decodeFilters($encodedFilters);
    }

    public function getAll(): array
    {
        return $this->filters;
    }

    public function empty(): bool
    {
        return empty($this->filters);
    }

    public function where(string $key, string $operator, $value): self
    {
        if ($value instanceof Collection) {
            $value = $value->toArray();
        }
        $this->filters[] = [
            'key' => $key,
            'operator' => $operator,
            'value' => $value,
        ];
        return $this;
    }

    public function getAndRemove(
        string $key,
        string $operator = null,
        $value = null,
    ): ?array {
        // use func_get_args as "null" is a valid value, need
        // to check whether if it was actually passed by user
        $args = func_get_args();

        foreach ($this->filters as $key => $filter) {
            if (
                $filter['key'] === $args[0] &&
                (!isset($args[1]) || $filter['operator'] === $args[1]) &&
                (!isset($args[2]) || $filter['value'] === $args[2])
            ) {
                unset($this->filters[$key]);
                return $filter;
            }
        }

        return null;
    }

    private function decodeFilters(?string $filterString): array
    {
        if ($filterString) {
            $filters = json_decode(
                base64_decode(urldecode($filterString)),
                true,
            );
            return collect($filters)
                ->flatMap(fn($filter) => $this->normalizeFilter($filter))
                ->filter()
                ->toArray();
        } else {
            return [];
        }
    }

    private function normalizeFilter(array $filter): ?array
    {
        $value = $filter['value'] ?? null;
        $operator = $filter['operator'] ?? '=';

        $value = $this->replaceValuePlaceholders($value);

        if (is_array($value)) {
            // filtering by normalized model
            if (isset($value['id'])) {
                $value = $this->replaceValuePlaceholders($value['id']);

                // "value" contains both value and operator
            } elseif (array_key_exists('value', $value)) {
                $operator = $value['operator'] ?? $operator;
                $value = $this->replaceValuePlaceholders($value['value']);
            }
        }

        $filters = [
            // preserve any extra keys that might be present
            array_merge($filter, [
                'key' => $filter['key'],
                'operator' => $operator,
                'value' => $value,
            ]),
        ];

        // filter might have some extra static filters, for example to restrict by model type
        if (isset($filter['extraFilters'])) {
            $filters[] = $filter['extraFilters'];
        }

        return $filters;
    }

    private function replaceValuePlaceholders($value)
    {
        if ($value === '{authId}') {
            return Auth::id();
        }
        return $value;
    }
}
