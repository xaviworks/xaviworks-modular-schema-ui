<?php

namespace XaviWorks\ModularSchemaUi\State;

final class RequestState
{
    private function __construct(
        private readonly ?string $sort,
        private readonly string $direction,
        private readonly ?string $search,
        /** @var array<string, scalar> */
        private readonly array $filters,
        private readonly int $page,
        private readonly int $perPage,
    ) {}

    /** @param list<string> $sortableColumns */
    /** @param list<string> $sortableColumns @param list<string> $filterNames */
    public static function from(array $input, array $sortableColumns, array $filterNames = []): self
    {
        $requestedSort = is_string($input['sort'] ?? null) ? $input['sort'] : null;
        $sort = in_array($requestedSort, $sortableColumns, true) ? $requestedSort : null;

        $requestedDirection = is_string($input['direction'] ?? null)
            ? strtolower($input['direction'])
            : 'asc';

        $direction = in_array($requestedDirection, ['asc', 'desc'], true)
            ? $requestedDirection
            : 'asc';

        $requestedSearch = is_string($input['search'] ?? null)
            ? trim($input['search'])
            : null;

        $search = $requestedSearch === null || $requestedSearch === ''
            ? null
            : mb_substr($requestedSearch, 0, 200);

        $requestedFilters = is_array($input['filters'] ?? null) ? $input['filters'] : [];
        $filters = [];

        foreach ($filterNames as $filterName) {
            $value = $requestedFilters[$filterName] ?? null;

            if (is_scalar($value) && (string) $value !== '') {
                $filters[$filterName] = $value;
            }
        }

        $page = filter_var($input['page'] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]) ?: 1;

        $perPage = filter_var($input['per_page'] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]) ?: 15;

        return new self($sort, $direction, $search, $filters, $page, $perPage);
    }

    public function sort(): ?string
    {
        return $this->sort;
    }

    public function direction(): string
    {
        return $this->direction;
    }

    public function search(): ?string
    {
        return $this->search;
    }

    /** @return array<string, scalar> */
    public function filters(): array
    {
        return $this->filters;
    }

    public function page(): int
    {
        return $this->page;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }
}
