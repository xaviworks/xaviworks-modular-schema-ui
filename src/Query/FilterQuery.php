<?php

namespace XaviWorks\ModularSchemaUi\Query;

use Illuminate\Database\Query\Builder;
use XaviWorks\ModularSchemaUi\Contracts\Filter;
use XaviWorks\ModularSchemaUi\State\RequestState;
use XaviWorks\ModularSchemaUi\Tables\Table;

final class FilterQuery
{
    public function apply(Builder $query, Table $table, RequestState $state): Builder
    {
        $values = $state->filters();

        foreach ($table->getFilters() as $filter) {
            /** @var Filter $filter */
            if (array_key_exists($filter->name(), $values)) {
                $query = $filter->apply($query, $values[$filter->name()]);
            }
        }

        return $query;
    }
}
