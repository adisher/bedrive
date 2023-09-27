<?php

namespace Common\Database\Datasource;

use Common\Database\Datasource\Filters\AlgoliaFilterer;
use Common\Database\Datasource\Filters\ElasticFilterer;
use Common\Database\Datasource\Filters\MeilisearchFilterer;
use Common\Database\Datasource\Filters\MysqlFilterer;
use Common\Database\Datasource\Filters\TntFilterer;
use Common\Search\Searchable;
use Common\Settings\Settings;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Expression;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Scout\Builder as ScoutBuilder;
use Matchish\ScoutElasticSearch\Engines\ElasticSearchEngine;
use const App\Providers\WORKSPACED_RESOURCES;

class Datasource
{
    protected EloquentBuilder|Relation $builder;
    protected Model $model;
    protected array $params;
    protected bool $queryBuilt = false;
    protected DatasourceFilters $filters;
    public array|null|false $order = null;
    protected ?ScoutBuilder $scoutBuilder;

    public function __construct(
        $model,
        array $params,
        DatasourceFilters $filters = null,
        protected string $filtererName = 'mysql',
    ) {
        $this->model = $model->getModel();
        $this->params = $this->toCamelCase($params);
        $this->builder = $model->newQuery();
        $this->filters =
            $filters ?? new DatasourceFilters($this->params['filters'] ?? null);
    }

    public function paginate(): AbstractPaginator
    {
        $this->buildQuery();
        $perPage = $this->limit();
        $page = (int) $this->param('page', 1);
        $method = $this->getPaginationMethod();

        if ($method === 'lengthAware') {
            return $this->scoutBuilder instanceof ScoutBuilder
                ? $this->scoutBuilder->paginate($perPage, 'page', $page)
                : $this->builder->paginate($perPage, ['*'], 'page', $page);
        } else {
            return $this->scoutBuilder instanceof ScoutBuilder
                ? $this->scoutBuilder->simplePaginate($perPage, 'page', $page)
                : $this->builder->simplePaginate(
                    $perPage,
                    ['*'],
                    'page',
                    $page,
                );
        }
    }

    public function get(): Collection
    {
        $this->buildQuery();
        return $this->builder->limit($this->limit())->get();
    }

    public function param(string $name, $default = null)
    {
        return Arr::get($this->params, Str::camel($name)) ?: $default;
    }

    public function buildQuery(): self
    {
        if ($this->queryBuilt) {
            return $this;
        }
        $with = array_filter(explode(',', $this->param('with', '')));
        $withCount = array_filter(explode(',', $this->param('withCount', '')));
        $searchTerm = $this->param('query');

        // load specified relations and counts
        if (!empty($with)) {
            $this->builder->with($with);
        }
        if (!empty($withCount)) {
            $this->builder->withCount($withCount);
        }

        $this->applyWorkspaceFilter();

        $filterer = $this->resolveFilterer($searchTerm);
        $this->scoutBuilder = (new $filterer(
            $this->builder,
            $this->filters,
            $searchTerm,
        ))->apply();

        // allow caller class to override order or
        // prevent it completely by setting "false"
        if ($this->order !== false) {
            $order = $this->getOrder();
            if (isset($order['col'])) {
                $this->builder->orderBy(
                    Str::snake($order['col']),
                    $order['dir'] ?? 'desc',
                );
            }
        }

        $this->queryBuilt = true;

        return $this;
    }

    private function resolveFilterer(string $searchTerm = null): string
    {
        $filtererName = $this->filtererName;
        if (
            !$searchTerm ||
            !in_array(Searchable::class, class_uses_recursive($this->model)) ||
            $filtererName === 'mysql'
        ) {
            return MysqlFilterer::class;
        } elseif ($filtererName === 'meilisearch') {
            return MeilisearchFilterer::class;
        } elseif ($filtererName === 'tntsearch') {
            return TntFilterer::class;
        } elseif ($filtererName === 'algolia') {
            return AlgoliaFilterer::class;
        } elseif ($filtererName === ElasticSearchEngine::class) {
            return ElasticFilterer::class;
        }

        return MysqlFilterer::class;
    }

    private function applyWorkspaceFilter(): void
    {
        if (
            !config('common.site.workspaces_integrated') ||
            !in_array(get_class($this->model), WORKSPACED_RESOURCES)
        ) {
            return;
        }

        if ($workspaceId = $this->param('workspaceId')) {
            $this->filters->where('workspace_id', '=', $workspaceId);
        } elseif ($userId = $this->param('userId')) {
            $this->filters
                ->where('user_id', '=', $userId)
                ->where('workspace_id', '=', 0);
        }
    }

    public function getOrder(): array
    {
        $defaultOrderDir = 'desc';
        $defaultOrderCol = 'updated_at';

        if (isset($this->order['col'])) {
            $orderCol = $this->order['col'];
            $orderDir = $this->order['dir'];
            // order might be a single string: "column|direction"
        } elseif ($specifiedOrder = $this->param('order')) {
            $parts = preg_split('(\||:)', $specifiedOrder);
            $orderCol = Arr::get($parts, 0, $defaultOrderCol);
            $orderDir = Arr::get($parts, 1, $defaultOrderDir);
            // order might be as separate params
        } elseif ($this->param('orderBy') || $this->param('orderDir')) {
            $orderCol = $this->param('orderBy');
            $orderDir = $this->param('orderDir');
            // try ordering be relevance, if it's a search query and
            // using mysql fulltext, finally default to "updated_at" column
        } elseif ($this->hasRelevanceColumn()) {
            $orderCol = 'relevance';
            $orderDir = 'desc';
        } else {
            $orderCol = $defaultOrderCol;
            $orderDir = $defaultOrderDir;
        }

        if ($orderCol !== 'relevance' && !Str::endsWith($orderCol, '_count')) {
            $orderCol = $this->builder->qualifyColumn($orderCol);
        }

        return [
            'col' => $orderCol,
            'dir' => $orderDir,
        ];
    }

    private function toCamelCase(array $params): array
    {
        return collect($params)
            ->keyBy(function ($value, $key) {
                return Str::camel($key);
            })
            ->toArray();
    }

    private function hasRelevanceColumn(): bool
    {
        return !!Arr::first($this->getQueryBuilder() ?? [], function ($col) {
            return $col instanceof Expression &&
                Str::endsWith($col->getValue(), 'AS relevance');
        });
    }

    private function limit(): int
    {
        if ($this->param('perPage')) {
            return $this->param('perPage');
        } else {
            return $this->getQueryBuilder()->limit ?? 15;
        }
    }

    private function getQueryBuilder(): QueryBuilder
    {
        $query = $this->builder->getQuery();
        if ($query instanceof EloquentBuilder) {
            $query = $query->getQuery();
        }
        return $query;
    }

    protected function getPaginationMethod(): string
    {
        $method = $this->param('paginate', 'lengthAware');

        if ($method !== 'simple') {
            $tables = explode(
                ',',
                app(Settings::class)->get('simple_pagination_tables', ''),
            );
            if (in_array($this->model->getTable(), $tables)) {
                $method = 'simple';
            }
        }

        return $method;
    }
}
