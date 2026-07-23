@php
    $httpMethod = strtoupper($method);
    $formMethod = in_array($httpMethod, ['GET', 'POST'], true) ? $httpMethod : 'POST';
@endphp

<form action="{{ $action }}" method="{{ $formMethod }}" {{ $attributes->merge(['class' => 'modular-form']) }}>
    @if ($formMethod !== 'GET')
        @csrf
    @endif

    @if ($httpMethod !== $formMethod)
        @method($httpMethod)
    @endif

    @foreach ($form->getFields() as $field)
        @php
            $fieldId = 'modular-'.$field->name();
            $fieldValue = old($field->name());
            $fieldAttributes = $field->htmlAttributes();
        @endphp

        <div class="modular-form-field">
            <label for="{{ $fieldId }}">
                {{ $field->labelText() }}
                @if ($field->isRequired())
                    <span aria-hidden="true">*</span>
                @endif
            </label>

            <input
                id="{{ $fieldId }}"
                name="{{ $field->name() }}"
                type="{{ $field->type() }}"
                value="{{ $fieldValue }}"
                @if ($field->isRequired()) required @endif
                @foreach ($fieldAttributes as $attribute => $value)
                    {{ $attribute }}="{{ $value }}"
                @endforeach
                @error($field->name()) aria-invalid="true" @enderror
            />

            @error($field->name())
                <p role="alert">{{ $message }}</p>
            @enderror
        </div>
    @endforeach

    <button type="submit">{{ $submitLabel }}</button>
</form>
