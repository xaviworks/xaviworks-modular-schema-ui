<?php

namespace XaviWorks\ModularSchemaUi\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;
use XaviWorks\ModularSchemaUi\Forms\Form as ModularForm;

final class Form extends Component
{
    public function __construct(
        public ModularForm $form,
        public ?string $action = null,
        public string $method = 'POST',
        public string $submitLabel = 'Submit',
    ) {}

    public function render(): View
    {
        return view('modular-schema-ui::forms.form');
    }
}
