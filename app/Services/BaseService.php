<?php

namespace App\Services;

use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class BaseService
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get a query builder instance for the model.
     */
    public function query(array $allowedFilters = [], array $allowedSorts = []): QueryBuilder
    {
        return QueryBuilder::for($this->model)
            ->allowedFilters($allowedFilters ?: $this->getSearchableColumns())
            ->allowedSorts($allowedSorts ?: $this->getSearchableColumns());
    }

    /**
     * Get searchable columns from the model.
     */
    protected function getSearchableColumns(): array
    {
        return $this->model->getFillable();
    }
}
