<div class="modular-table-wrapper" role="region" aria-label="Data table" tabindex="0">
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
