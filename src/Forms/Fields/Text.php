<?php

namespace XaviWorks\ModularSchemaUi\Forms\Fields;

use XaviWorks\ModularSchemaUi\Forms\Field;

final class Text extends Field
{
    public function type(): string
    {
        return 'text';
    }
}
