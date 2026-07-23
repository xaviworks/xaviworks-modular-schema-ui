<form wire:submit="save">
    @foreach ($form['fields'] as $field)
        <label>
            {{ $field['label'] }}
            <input wire:model="data.{{ $field['name'] }}" type="{{ $field['type'] }}" @required($field['required'])>
        </label>
    @endforeach
    <button type="submit">Submit</button>
</form>
