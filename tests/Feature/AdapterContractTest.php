<?php

namespace XaviWorks\ModularSchemaUi\Tests\Feature;

use XaviWorks\ModularSchemaUi\Forms\Fields\Text;
use XaviWorks\ModularSchemaUi\Forms\Form;
use XaviWorks\ModularSchemaUi\Support\SchemaPayload;
use XaviWorks\ModularSchemaUi\Tables\Columns\TextColumn;
use XaviWorks\ModularSchemaUi\Tables\Table;
use XaviWorks\ModularSchemaUi\Tests\TestCase;

final class AdapterContractTest extends TestCase
{
    public function test_form_and_table_payloads_have_a_stable_adapter_contract(): void
    {
        $form = SchemaPayload::form(Form::make(
            Text::make('name')->required()->maxLength(255),
        ));

        $table = SchemaPayload::table(Table::make(
            TextColumn::make('name')->sortable()->searchable(),
        ));

        $this->assertSame(['fields', 'values'], array_keys($form));
        $this->assertSame(
            ['name', 'label', 'type', 'required', 'default', 'placeholder', 'help', 'readonly', 'disabled', 'attributes', 'options', 'rules', 'validation'],
            array_keys($form['fields'][0]),
        );
        $this->assertSame(['columns', 'filters', 'actions', 'records', 'emptyMessage', 'perPageOptions', 'controls', 'pagination'], array_keys($table));
    }

    public function test_react_and_vue_adapters_include_shared_types(): void
    {
        $root = dirname(__DIR__, 2);

        $reactTypes = $root.'/stubs/frontend/react/ModularSchema.types.ts.stub';
        $vueTypes = $root.'/stubs/frontend/vue/ModularSchema.types.ts.stub';

        $this->assertFileExists($reactTypes);
        $this->assertFileExists($vueTypes);
        $this->assertStringContainsString('export type FormPayload', file_get_contents($reactTypes));
        $this->assertStringContainsString('export type TablePayload', file_get_contents($reactTypes));
        $this->assertStringContainsString('export type FormPayload', file_get_contents($vueTypes));
        $this->assertStringContainsString('export type TablePayload', file_get_contents($vueTypes));
    }

    public function test_all_form_adapters_use_validation_metadata(): void
    {
        $root = dirname(__DIR__, 2);

        $adapterFiles = [
            $root.'/stubs/frontend/react/ModularForm.tsx.stub',
            $root.'/stubs/frontend/vue/ModularForm.vue.stub',
            $root.'/stubs/frontend/livewire/modular-form.blade.php.stub',
        ];

        foreach ($adapterFiles as $adapterFile) {
            $this->assertFileExists($adapterFile);
            $this->assertStringContainsString('validation', file_get_contents($adapterFile));
        }
    }
}
