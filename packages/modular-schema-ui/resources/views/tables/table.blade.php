<div class="modular-table-wrapper" role="region" aria-label="Data table" tabindex="0">
    @if ($table->getFilters()->isNotEmpty() || $table->searchableColumnNames() !== [])
        @php
            $activeFilters = $state?->filters() ?? request()->input('filters', []);
            $activeSearch = $state?->search() ?? request()->input('search');
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
</div>
