<?php

namespace Common\Database\Metrics;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

class Partition extends BaseMetric
{
    public function __construct(
        Model|Builder|string $model,
        public string $groupBy,
        MetricDateRange $dateRange,
        string|Expression|null $column = null,
        ?string $dateColumn = null,
        int $limit = 50,
        protected array $additionalColumns = [],
    ) {
        parent::__construct($model, $dateRange, $column, $dateColumn, $limit);
    }

    protected function aggregate(string $function): array
    {
        $select = [
            $this->groupBy,
            DB::raw("{$function}({$this->getWrappedColumn()}) as aggregate"),
            ...$this->additionalColumns,
        ];

        $results = $this->query
            ->select($select)
            ->groupBy($this->groupBy, ...$this->additionalColumns)
            ->when(
                $this->dateRange,
                fn($query) => $query->whereBetween($this->dateColumn, [
                    $this->dateRange->start,
                    $this->dateRange->end,
                ]),
            )
            ->limit($this->limit)
            ->get();

        $data = $results->map(function ($result) {
            $finalResult = [
                'label' => $this->getLabel($result),
                'value' => $this->round($result->aggregate),
            ];
            foreach ($this->additionalColumns as $column) {
              $finalResult[$column] = $result->{$column};
            }
            return $finalResult;
        });
        $total = $data->sum('value');
        $data = $data
            ->map(function ($item) use ($total) {
                $item['percentage'] = round((100 * $item['value']) / $total, 1);
                return $item;
            })
            ->sortByDesc('value')
            ->values();

        return $data->all();
    }

    protected function getLabel(Model $result): string
    {
        $label = with(
            $result->{last(explode('.', $this->groupBy))},
            fn($key) => $key,
        );

        return __(ucfirst($label));
    }
}
