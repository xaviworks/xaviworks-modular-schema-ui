<?php

namespace XaviWorks\ModularSchemaUi\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LogicException;
use XaviWorks\ModularSchemaUi\Forms\Fields\Date;
use XaviWorks\ModularSchemaUi\Forms\Fields\Email;
use XaviWorks\ModularSchemaUi\Forms\Fields\Number;
use XaviWorks\ModularSchemaUi\Forms\Fields\Text;
use XaviWorks\ModularSchemaUi\Forms\Form;
use XaviWorks\ModularSchemaUi\Query\QueryPipeline;
use XaviWorks\ModularSchemaUi\Resources\UiSchema;
use XaviWorks\ModularSchemaUi\State\RequestState;
use XaviWorks\ModularSchemaUi\Support\SchemaPayload;
use XaviWorks\ModularSchemaUi\Tables\Action;
use XaviWorks\ModularSchemaUi\Tables\Columns\TextColumn;
use XaviWorks\ModularSchemaUi\Tables\Filters\TextFilter;
use XaviWorks\ModularSchemaUi\Tables\Table;
use XaviWorks\ModularSchemaUi\Tests\TestCase;

final class GeneratorUser extends Model
{
    protected $table = 'generator_users';
}

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
        $this->assertSame([
            'required' => true,
            'nullable' => false,
            'maxLength' => 256,
        ], $form->toArray()['fields'][0]['validation']);
        $this->assertSame([
            'required' => false,
            'nullable' => false,
            'email' => true,
        ], $form->toArray()['fields'][1]['validation']);
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

    public function test_forms_and_tables_support_shortcut_declarations(): void
    {
        $form = Form::make(
            Text::make('name')->required(),
            Email::make('email'),
        );

        $table = Table::make(
            TextColumn::make('name')->searchable(),
        );

        $this->assertCount(2, $form->getFields());
        $this->assertCount(1, $table->getColumns());
    }

    public function test_a_feature_can_define_multiple_named_forms_and_tables(): void
    {
        $ui = new class extends UiSchema
        {
            public function form(Form $form): Form
            {
                return $form->fields(Text::make('name'));
            }

            public function createForm(Form $form): Form
            {
                return $form->fields(Text::make('name')->required());
            }

            public function table(Table $table): Table
            {
                return $table->columns(TextColumn::make('name'));
            }

            public function archiveTable(Table $table): Table
            {
                return $table->columns(TextColumn::make('archived_at'));
            }
        };

        $this->assertFalse($ui->resolveForm()->toArray()['fields'][0]['required']);
        $this->assertTrue($ui->resolveForm('create')->toArray()['fields'][0]['required']);
        $this->assertSame('name', $ui->resolveTable()->toArray()['columns'][0]['name']);
        $this->assertSame('archived_at', $ui->resolveTable('archive')->toArray()['columns'][0]['name']);
    }

    public function test_a_missing_named_definition_has_a_clear_error(): void
    {
        $ui = new class extends UiSchema {};

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('UI form [missing] is not defined');

        $ui->resolveForm('missing');
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

    public function test_create_command_can_preview_a_generated_resource(): void
    {
        Schema::create('generator_users', function ($table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
        });

        $this->artisan('modular:create', [
            'name' => 'GeneratorUser',
            '--model' => GeneratorUser::class,
            '--table' => 'generator_users',
            '--frontend' => 'react',
            '--dry-run' => true,
        ])->expectsOutput('Would create: '.app_path('Modular/GeneratorUsers/GeneratorUserSchema.php'))
            ->expectsOutput('Would create: '.app_path('Http/Controllers/GeneratorUserController.php'))
            ->assertSuccessful();

        $this->assertFalse(Schema::hasTable('nonexistent_generated_table'));
    }

    public function test_query_pipeline_applies_search_filters_sorting_and_pagination(): void
    {
        Schema::create('query_users', function ($table): void {
            $table->id();
            $table->string('name');
            $table->string('status');
        });

        DB::table('query_users')->insert([
            ['name' => 'Alice', 'status' => 'active'],
            ['name' => 'Bob', 'status' => 'inactive'],
            ['name' => 'Alicia', 'status' => 'active'],
        ]);

        $table = Table::make(
            TextColumn::make('name')->sortable()->searchable(),
        )->filters(
            TextFilter::make('status'),
        )->perPageOptions([1, 10]);

        $state = RequestState::from([
            'search' => 'Ali',
            'sort' => 'name',
            'direction' => 'desc',
            'filters' => ['status' => 'active'],
            'per_page' => 1,
        ], $table->sortableColumnNames(), $table->filterNames());

        $paginator = QueryPipeline::make()->resolve(
            DB::table('query_users'),
            $table,
            $state,
            $table->getPerPageOptions(),
        );

        $this->assertSame(2, $paginator->total());
        $this->assertSame('Alicia', $paginator->items()[0]->name);
        $this->assertSame(1, $paginator->perPage());
    }
}
