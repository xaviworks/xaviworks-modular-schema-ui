<?php

namespace XaviWorks\ModularSchemaUi\Forms\Fields;

use XaviWorks\ModularSchemaUi\Forms\Field;

final class Date extends Field
{
    public function type(): string
    {
        return 'date';
    }
}
