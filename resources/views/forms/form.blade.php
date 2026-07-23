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
            $helpId = $fieldId.'-help';
            $errorId = $fieldId.'-error';
            $fieldValue = $form->valueFor($field);
            $fieldAttributes = $field->htmlAttributes();
            $hasError = $errors->has($field->name());
            $describedBy = collect([
                $field->helpTextValue() ? $helpId : null,
                $hasError ? $errorId : null,
            ])->filter()->implode(' ');
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
                    @if ($field->placeholderText()) placeholder="{{ $field->placeholderText() }}" @endif
                    @if ($field->isReadonly()) readonly @endif
                    @if ($field->isDisabled()) disabled @endif
                    @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
                    @if ($field->isRequired()) required @endif
                    @foreach ($fieldAttributes as $attribute => $value)
                        {{ $attribute }}="{{ $value }}"
                    @endforeach
                    @if ($hasError) aria-invalid="true" @endif
                >{{ $fieldValue }}</textarea>
            @elseif ($field->type() === 'select')
                <select
                    id="{{ $fieldId }}"
                    name="{{ $field->name() }}"
                    @if ($field->placeholderText()) aria-label="{{ $field->placeholderText() }}" @endif
                    @if ($field->isReadonly()) readonly @endif
                    @if ($field->isDisabled()) disabled @endif
                    @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
                    @if ($field->isRequired()) required @endif
                    @foreach ($fieldAttributes as $attribute => $value)
                        {{ $attribute }}="{{ $value }}"
                    @endforeach
                    @if ($hasError) aria-invalid="true" @endif
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
                    @if ($field->placeholderText()) placeholder="{{ $field->placeholderText() }}" @endif
                    @if ($field->isReadonly()) readonly @endif
                    @if ($field->isDisabled()) disabled @endif
                    @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
                    @if ($field->isRequired()) required @endif
                    @foreach ($fieldAttributes as $attribute => $value)
                        {{ $attribute }}="{{ $value }}"
                    @endforeach
                    @if ($hasError) aria-invalid="true" @endif
                />
            @endif

            @if ($field->helpTextValue())
                <p id="{{ $helpId }}">{{ $field->helpTextValue() }}</p>
            @endif

            @error($field->name())
                <p id="{{ $errorId }}" role="alert">{{ $message }}</p>
            @enderror
        </div>
    @endforeach

    <button type="submit">{{ $submitLabel }}</button>
</form>
