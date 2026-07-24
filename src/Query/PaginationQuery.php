<?php

namespace XaviWorks\ModularSchemaUi\Query;

use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use XaviWorks\ModularSchemaUi\State\RequestState;

final class PaginationQuery
{
    /** @param list<int> $perPageOptions */
    public function apply(
        Builder $query,
        RequestState $state,
        array $perPageOptions = [10, 25, 50],
        int $maximumPerPage = 100,
    ): LengthAwarePaginator {
        $allowedPerPage = array_values(array_filter(
            $perPageOptions,
            fn (int $option): bool => $option > 0 && $option <= $maximumPerPage,
        ));

        $perPage = in_array($state->perPage(), $allowedPerPage, true)
            ? $state->perPage()
            : ($allowedPerPage[0] ?? min(15, $maximumPerPage));

        return $query
            ->paginate($perPage, ['*'], 'page', $state->page())
            ->withQueryString();
    }
}
