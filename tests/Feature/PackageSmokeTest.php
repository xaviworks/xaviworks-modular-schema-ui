<?php

namespace XaviWorks\ModularSchemaUi\Tests\Feature;

use XaviWorks\ModularSchemaUi\Forms\Fields\Date;
use XaviWorks\ModularSchemaUi\Forms\Fields\Email;
use XaviWorks\ModularSchemaUi\Forms\Fields\Number;
use XaviWorks\ModularSchemaUi\Forms\Fields\Text;
use XaviWorks\ModularSchemaUi\Forms\Form;
use XaviWorks\ModularSchemaUi\Support\SchemaPayload;
use XaviWorks\ModularSchemaUi\Tables\Action;
use XaviWorks\ModularSchemaUi\Tables\Columns\TextColumn;
use XaviWorks\ModularSchemaUi\Tables\Table;
use XaviWorks\ModularSchemaUi\Tests\TestCase;

final class PackageSmokeTest extends TestCase
{
    public function test_package_views_are_loaded(): void
    {
        $this->assertTrue(view()->exists('modular-schema-ui::forms.form'));
        $this->assertTrue(view()->exists('modular-schema-ui::tables.table'));
    }

    public function test_form_schema_can_be_consumed_by_any_frontend(): void
    {
        $form = Form::make()->fields([
            Text::make('name')->required()->default('Junn'),
        ]);

        $payload = SchemaPayload::form($form);

        $this->assertSame('text', $payload['fields'][0]['type']);
        $this->assertTrue($payload['fields'][0]['required']);
        $this->assertSame('Junn', $payload['values']['name']);
    }

    public function test_form_schema_exposes_validation_rules(): void
    {
        $form = Form::make()->fields([
            Text::make('name')->required()->maxLength(256),
            Email::make('email'),
        ]);

        $this->assertSame(['required', 'max:256'], $form->validationRules()['name']);
        $this->assertSame(['email'], $form->validationRules()['email']);
        $this->assertSame(['required', 'max:256'], $form->toArray()['fields'][0]['rules']);
    }

    public function test_table_schema_contains_frontend_neutral_records(): void
    {
        $table = Table::make()
            ->columns([TextColumn::make('name')->sortable()->searchable()])
            ->records([['name' => 'Modular UI']]);

        $payload = SchemaPayload::table($table);

        $this->assertSame('name', $payload['columns'][0]['name']);
        $this->assertTrue($payload['columns'][0]['sortable']);
        $this->assertSame('Modular UI', $payload['records'][0]['name']);
    }

    public function test_table_schema_exposes_reusable_row_actions(): void
    {
        $table = Table::make()
            ->columns([TextColumn::make('name')])
            ->actions([
                Action::make('edit')->url('/users/{id}'),
                Action::make('delete')->httpMethod('DELETE')->url('/users/{id}')->confirm('Delete this record?'),
            ])
            ->records([['id' => 7, 'name' => 'Modular UI']]);

        $payload = SchemaPayload::table($table);

        $this->assertSame(7, $payload['records'][0]['id']);
        $this->assertSame('DELETE', $payload['actions'][1]['method']);
        $this->assertSame('Delete this record?', $payload['actions'][1]['confirm']);
    }

    public function test_common_field_types_expose_frontend_input_types(): void
    {
        $form = Form::make()->fields([
            Date::make('starts_at'),
            Number::make('capacity'),
        ]);

        $this->assertSame('date', $form->toArray()['fields'][0]['type']);
        $this->assertSame('number', $form->toArray()['fields'][1]['type']);
    }

    public function test_install_command_can_select_a_react_adapter_without_writing_files(): void
    {
        $this->artisan('modular:install', [
            '--frontend' => 'react',
            '--dry-run' => true,
        ])->expectsOutput('XaviWorks Modular Schema UI')
            ->expectsOutput('Created by Junn Xavier Adalid')
            ->expectsOutput('Selected Modular frontend: react')
            ->assertSuccessful();
    }
}
