<?php

namespace XaviWorks\ModularSchemaUi\Forms\Fields;

use XaviWorks\ModularSchemaUi\Forms\Field;

final class Email extends Field
{
    public function type(): string
    {
        return 'email';
    }
}
