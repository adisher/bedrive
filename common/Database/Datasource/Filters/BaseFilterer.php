<?php

namespace Common\Database\Datasource\Filters;

use Common\Database\Datasource\DatasourceFilters;
use Laravel\Scout\Builder as ScoutBuilder;

abstract class BaseFilterer
{
    public function __construct(
        protected $query,
        protected DatasourceFilters $filters,
        protected string|null $searchTerm = null
    ) {
    }

    abstract public function apply(): ?ScoutBuilder;
}
