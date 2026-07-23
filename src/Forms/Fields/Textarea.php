<?php

namespace XaviWorks\ModularSchemaUi\Forms\Fields;

use XaviWorks\ModularSchemaUi\Forms\Field;

final class Textarea extends Field
{
    public function type(): string
    {
        return 'textarea';
    }
}
