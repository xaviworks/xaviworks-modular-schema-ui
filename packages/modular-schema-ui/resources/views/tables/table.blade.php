<div class="modular-table-wrapper" role="region" aria-label="Data table" tabindex="0">
    @if ($table->getFilters()->isNotEmpty() || $table->searchableColumnNames() !== [] || $table->getPaginator())
        @php
            $activeFilters = $state?->filters() ?? request()->input('filters', []);
            $activeSearch = $state?->search() ?? request()->input('search');
            $activePerPage = $state?->perPage() ?? request()->input('per_page', $table->getPerPageOptions()[0] ?? 15);
        @endphp

        <form method="GET" action="{{ $filterAction ?? request()->url() }}" class="modular-table-controls">
            @if ($table->searchableColumnNames() !== [])
                <label for="modular-search">Search</label>
                <input id="modular-search" name="search" type="search" value="{{ $activeSearch }}" />
            @endif

            @foreach ($table->getFilters() as $filter)
                <label for="modular-filter-{{ $filter->name() }}">{{ $filter->labelText() }}</label>
                <select id="modular-filter-{{ $filter->name() }}" name="filters[{{ $filter->name() }}]">
                    <option value="">All</option>
                    @foreach ($filter->optionValues() as $optionValue => $optionLabel)
                        <option
                            value="{{ $optionValue }}"
                            @selected((string) ($activeFilters[$filter->name()] ?? '') === (string) $optionValue)
                        >
                            {{ $optionLabel }}
                        </option>
                    @endforeach
                </select>
            @endforeach

            @if ($table->getPaginator())
                <label for="modular-per-page">Per page</label>
                <select id="modular-per-page" name="per_page">
                    @foreach ($table->getPerPageOptions() as $perPage)
                        <option value="{{ $perPage }}" @selected((int) $activePerPage === $perPage)>
                            {{ $perPage }}
                        </option>
                    @endforeach
                </select>
            @endif

            <button type="submit">Apply</button>
            <a href="{{ $filterAction ?? request()->url() }}">Reset</a>
        </form>
    @endif

    <table {{ $attributes->merge(['class' => 'modular-table']) }}>
        <thead>
            <tr>
                @foreach ($table->getColumns() as $column)
                    <th scope="col">{{ $column->labelText() }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($table->getRecords() as $record)
                <tr>
                    @foreach ($table->getColumns() as $column)
                        <td>{{ $column->displayValue($record) }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ max(1, $table->getColumns()->count()) }}">
                        {{ $table->emptyStateMessage() }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($table->getPaginator())
        @php($paginator = $table->getPaginator())

        <nav aria-label="Pagination">
            <p>
                Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }}
                of {{ $paginator->total() }} results
            </p>

            @if ($paginator->onFirstPage())
                <span aria-disabled="true">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}">Previous</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}">Next</a>
            @else
                <span aria-disabled="true">Next</span>
            @endif
        </nav>
    @endif
</div>
