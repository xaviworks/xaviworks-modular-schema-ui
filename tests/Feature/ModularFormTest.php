<?php

use Illuminate\Support\Facades\DB;
use XaviWorks\ModularSchemaUi\Forms\Fields\Email;
use XaviWorks\ModularSchemaUi\Forms\Fields\Hidden;
use XaviWorks\ModularSchemaUi\Forms\Fields\Password;
use XaviWorks\ModularSchemaUi\Forms\Fields\Select;
use XaviWorks\ModularSchemaUi\Forms\Fields\Text;
use XaviWorks\ModularSchemaUi\Forms\Fields\Textarea;
use XaviWorks\ModularSchemaUi\Forms\Form;
use XaviWorks\ModularSchemaUi\Query\QueryPipeline;
use XaviWorks\ModularSchemaUi\State\RequestState;
use XaviWorks\ModularSchemaUi\Tables\Columns\BooleanColumn;
use XaviWorks\ModularSchemaUi\Tables\Columns\TextColumn;
use XaviWorks\ModularSchemaUi\Tables\Filters\BooleanFilter;
use XaviWorks\ModularSchemaUi\Tables\Filters\SelectFilter;
use XaviWorks\ModularSchemaUi\Tables\Table;

it('renders a modular form with escaped values and csrf protection', function (): void {
    $form = Form::make()->fields([
        Text::make('name')->label('Full Name')->required(),
        Email::make('email')->required(),
    ]);

    $response = $this->withViewErrors([])->view('modular-form-test', compact('form'));

    $response->assertSee('name="_token"', false)
        ->assertSee('name="name"', false)
        ->assertSee('type="email"', false)
        ->assertSee('Full Name')
        ->assertSee('required', false);
});

it('exposes the modular form workbench route', function (): void {
    $this->get('/modular/forms')
        ->assertSuccessful()
        ->assertSee('User details')
        ->assertSee('name="name"', false);
});

it('renders the expanded modular field set safely', function (): void {
    $form = Form::make()->fields([
        Textarea::make('bio'),
        Select::make('role')->options([
            'admin' => 'Administrator',
            'user' => 'User',
        ]),
        Password::make('password'),
        Hidden::make('token'),
    ]);

    $response = $this->withViewErrors([])->view('modular-form-test', compact('form'));

    $response->assertSee('<textarea', false)
        ->assertSee('Administrator')
        ->assertSee('value="admin"', false)
        ->assertSee('type="password"', false)
        ->assertSee('type="hidden"', false);
});

it('resolves model values and field defaults without populating passwords', function (): void {
    $form = Form::make()
        ->model((object) [
            'name' => 'Junn Xavier',
            'bio' => 'Building modular Laravel tools.',
            'role' => 'admin',
            'password' => 'never-render-this',
        ])
        ->fields([
            Text::make('name'),
            Textarea::make('bio'),
            Select::make('role')->options(['admin' => 'Administrator']),
            Password::make('password'),
            Text::make('fallback')->default('Default value'),
        ]);

    $response = $this->withViewErrors([])->view('modular-form-test', compact('form'));

    $response->assertSee('value="Junn Xavier"', false)
        ->assertSee('Building modular Laravel tools.')
        ->assertSee('value="admin" selected', false)
        ->assertSee('value="Default value"', false)
        ->assertDontSee('never-render-this');
});

it('renders accessible modular field descriptions and states', function (): void {
    $form = Form::make()->fields([
        Text::make('name')
            ->placeholder('Your full name')
            ->helpText('Use the name shown on official records.')
            ->readonly()
            ->disabled(),
    ]);

    $response = $this->withViewErrors(['name' => 'Your name is required.'])
        ->view('modular-form-test', compact('form'));

    $response->assertSee('placeholder="Your full name"', false)
        ->assertSee('readonly', false)
        ->assertSee('disabled', false)
        ->assertSee('modular-name-help')
        ->assertSee('modular-name-error')
        ->assertSee('aria-invalid="true"', false)
        ->assertSee('Your name is required.');
});

it('renders modular table rows and boolean values', function (): void {
    $table = Table::make()
        ->records([
            ['name' => 'Junn Xavier', 'active' => true],
        ])
        ->columns([
            TextColumn::make('name'),
            BooleanColumn::make('active'),
        ]);

    $response = $this->view('modular-table-test', compact('table'));

    $response->assertSee('<table', false)
        ->assertSee('Junn Xavier')
        ->assertSee('Yes')
        ->assertSee('scope="col"', false);
});

it('renders a modular table empty state', function (): void {
    $table = Table::make()
        ->emptyMessage('No users are available.')
        ->columns([TextColumn::make('name')]);

    $this->view('modular-table-test', compact('table'))
        ->assertSee('No users are available.');
});

it('normalizes only allowlisted modular sort state', function (): void {
    $table = Table::make()->columns([
        TextColumn::make('name')->sortable(),
        TextColumn::make('email'),
    ]);

    expect($table->sortableColumnNames())->toBe(['name']);

    $valid = RequestState::from([
        'sort' => 'name',
        'direction' => 'DESC',
    ], $table->sortableColumnNames());

    expect($valid->sort())->toBe('name')
        ->and($valid->direction())->toBe('desc');

    $invalid = RequestState::from([
        'sort' => 'email',
        'direction' => 'sideways',
    ], $table->sortableColumnNames());

    expect($invalid->sort())->toBeNull()
        ->and($invalid->direction())->toBe('asc');
});

it('applies only allowlisted modular sorting to a query', function (): void {
    $table = Table::make()->columns([
        TextColumn::make('name')->sortable(),
        TextColumn::make('email'),
    ]);

    $query = QueryPipeline::make()->sort(
        DB::query()->from('users'),
        $table,
        RequestState::from(['sort' => 'name', 'direction' => 'desc'], $table->sortableColumnNames()),
    );

    expect($query->toSql())->toContain('order by "name" desc');

    $untrustedQuery = QueryPipeline::make()->sort(
        DB::query()->from('users'),
        $table,
        RequestState::from(['sort' => 'email', 'direction' => 'desc'], $table->sortableColumnNames()),
    );

    expect($untrustedQuery->toSql())->not->toContain('order by');
});

it('applies grouped search only to searchable modular columns', function (): void {
    $table = Table::make()->columns([
        TextColumn::make('name')->searchable(),
        TextColumn::make('email')->searchable(),
        TextColumn::make('secret'),
    ]);

    expect($table->searchableColumnNames())->toBe(['name', 'email']);

    $state = RequestState::from([
        'search' => '  Junn%  ',
    ], $table->sortableColumnNames());

    expect($state->search())->toBe('Junn%');

    $query = QueryPipeline::make()->search(
        DB::query()->from('users'),
        $table,
        $state,
    );

    expect($query->toSql())->toContain('"name" like ?')
        ->and($query->toSql())->toContain('"email" like ?')
        ->and($query->toSql())->not->toContain('"secret"')
        ->and($query->getBindings())->toBe(['%Junn\\%%', '%Junn\\%%']);
});

it('applies only allowlisted modular filters to a query', function (): void {
    $table = Table::make()
        ->filters([
            SelectFilter::make('role')->options([
                'admin' => 'Administrator',
                'user' => 'User',
            ]),
            BooleanFilter::make('active'),
        ]);

    $state = RequestState::from([
        'filters' => [
            'role' => 'admin',
            'active' => 'true',
            'secret' => 'ignored',
        ],
    ], [], $table->filterNames());

    expect($state->filters())->toBe([
        'role' => 'admin',
        'active' => 'true',
    ]);

    $query = QueryPipeline::make()->filters(
        DB::query()->from('users'),
        $table,
        $state,
    );

    expect($query->toSql())->toContain('"role" = ?')
        ->and($query->toSql())->toContain('"active" = ?')
        ->and($query->getBindings())->toBe(['admin', true]);
});
