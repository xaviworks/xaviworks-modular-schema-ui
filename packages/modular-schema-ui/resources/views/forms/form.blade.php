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
            $fieldValue = $field->canRestoreValue() ? old($field->name()) : null;
            $fieldAttributes = $field->htmlAttributes();
        @endphp

        <div class="modular-form-field">
            @if ($field->type() !== 'hidden')
                <label for="{{ $fieldId }}">
                    {{ $field->labelText() }}
                    @if ($field->isRequired())
                        <span aria-hidden="true">*</span>
                    @endif
                </label>
            @endif

            @if ($field->type() === 'textarea')
                <textarea
                    id="{{ $fieldId }}"
                    name="{{ $field->name() }}"
                    @if ($field->isRequired()) required @endif
                    @foreach ($fieldAttributes as $attribute => $value)
                        {{ $attribute }}="{{ $value }}"
                    @endforeach
                    @error($field->name()) aria-invalid="true" @enderror
                >{{ $fieldValue }}</textarea>
            @elseif ($field->type() === 'select')
                <select
                    id="{{ $fieldId }}"
                    name="{{ $field->name() }}"
                    @if ($field->isRequired()) required @endif
                    @foreach ($fieldAttributes as $attribute => $value)
                        {{ $attribute }}="{{ $value }}"
                    @endforeach
                    @error($field->name()) aria-invalid="true" @enderror
                >
                    @foreach ($field->optionValues() as $optionValue => $optionLabel)
                        <option value="{{ $optionValue }}" @selected((string) $fieldValue === (string) $optionValue)>
                            {{ $optionLabel }}
                        </option>
                    @endforeach
                </select>
            @else
                <input
                    id="{{ $fieldId }}"
                    name="{{ $field->name() }}"
                    type="{{ $field->type() }}"
                    @if ($field->type() !== 'password') value="{{ $fieldValue }}" @endif
                    @if ($field->isRequired()) required @endif
                    @foreach ($fieldAttributes as $attribute => $value)
                        {{ $attribute }}="{{ $value }}"
                    @endforeach
                    @error($field->name()) aria-invalid="true" @enderror
                />
            @endif

            @error($field->name())
                <p role="alert">{{ $message }}</p>
            @enderror
        </div>
    @endforeach

    <button type="submit">{{ $submitLabel }}</button>
</form>
