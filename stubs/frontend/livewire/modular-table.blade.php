<table>
    <thead><tr>@foreach ($table['columns'] as $column)<th>{{ $column['label'] }}</th>@endforeach</tr></thead>
    <tbody>
        @forelse ($table['records'] as $record)
            <tr>@foreach ($table['columns'] as $column)<td>{{ $record[$column['name']] ?? '' }}</td>@endforeach</tr>
        @empty
            <tr><td colspan="{{ count($table['columns']) }}">{{ $table['emptyMessage'] }}</td></tr>
        @endforelse
    </tbody>
</table>
