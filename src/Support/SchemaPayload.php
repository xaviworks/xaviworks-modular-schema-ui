<?php

namespace XaviWorks\ModularSchemaUi\Support;

use XaviWorks\ModularSchemaUi\Forms\Form;
use XaviWorks\ModularSchemaUi\Tables\Table;

/** Frontend-neutral payload helpers for Blade, React, Vue, and Livewire adapters. */
final class SchemaPayload
{
    /** @return array<string, mixed> */
    public static function form(Form $form): array
    {
        return $form->toArray();
    }

    /** @return array<string, mixed> */
    public static function table(Table $table): array
    {
        return $table->toArray();
    }
}
