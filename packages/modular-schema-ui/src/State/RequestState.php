<?php

namespace XaviWorks\ModularSchemaUi\State;

final class RequestState
{
    private function __construct(
        private readonly ?string $sort,
        private readonly string $direction,
    ) {}

    /** @param list<string> $sortableColumns */
    public static function from(array $input, array $sortableColumns): self
    {
        $requestedSort = is_string($input['sort'] ?? null) ? $input['sort'] : null;
        $sort = in_array($requestedSort, $sortableColumns, true) ? $requestedSort : null;

        $requestedDirection = is_string($input['direction'] ?? null)
            ? strtolower($input['direction'])
            : 'asc';

        $direction = in_array($requestedDirection, ['asc', 'desc'], true)
            ? $requestedDirection
            : 'asc';

        return new self($sort, $direction);
    }

    public function sort(): ?string
    {
        return $this->sort;
    }

    public function direction(): string
    {
        return $this->direction;
    }
}
