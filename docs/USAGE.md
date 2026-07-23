# XaviWorks Modular Schema UI

Practical usage guide for Laravel applications using the modular forms, tables,
filters, validation, and frontend adapters.

## Installation

```bash
composer require xaviworks/modular-schema-ui
php artisan modular:install --frontend=react
```

Supported adapters:

- `blade`
- `react`
- `vue`
- `livewire`

If `--frontend` is omitted, the installer detects the frontend from the host
application and falls back to Blade.

## Generate a modular resource

After running your migrations, generate a resource from an existing model and
table:

```bash
php artisan modular:create User --table=users --frontend=react --force
```

This creates a schema, controller, resource routes, and React pages:

```text
app/Modular/Users/UserSchema.php
app/Http/Controllers/UserController.php
routes/modular.php
resources/js/pages/users/index.tsx
resources/js/pages/users/create.tsx
resources/js/pages/users/edit.tsx
```

The command infers basic field types, required fields, column lengths, nullable
column presence filters, and default Edit/Delete actions.

To include Laravel policy checks in the generated controller:

```bash
php artisan modular:create User --table=users --frontend=react --authorize
```

Create and register the corresponding policy in the host application before
using `--authorize`.

## Define a form schema

```php
use XaviWorks\ModularSchemaUi\Forms\Fields\Email;
use XaviWorks\ModularSchemaUi\Forms\Fields\Password;
use XaviWorks\ModularSchemaUi\Forms\Fields\Text;
use XaviWorks\ModularSchemaUi\Forms\Form;
use XaviWorks\ModularSchemaUi\Resources\ResourceSchema;

final class UserSchema extends ResourceSchema
{
    public function form(Form $form): Form
    {
        return $form->fields([
            Text::make('name')->required()->maxLength(256),
            Email::make('email')->required(),
            Password::make('password')->required()->minLength(8),
        ]);
    }
}
```

Available fields:

```php
Text::make('name');
Email::make('email');
Password::make('password');
Textarea::make('bio');
Select::make('role')->options(['admin' => 'Administrator']);
Date::make('starts_on');
DateTime::make('starts_at');
Number::make('capacity');
Checkbox::make('active');
Hidden::make('token');
```

Field configuration:

```php
Text::make('nickname')
    ->label('Display name')
    ->placeholder('Optional name')
    ->helpText('Shown publicly')
    ->nullable()
    ->rules(['string', 'max:80']);
```

## Validation

Validation rules are declared beside the field and are available through the
schema:

```php
$rules = (new UserSchema)->validationRules();
```

Supported helpers include:

```php
Text::make('name')->required();
Text::make('name')->maxLength(256);
Text::make('name')->minLength(2);
Text::make('nickname')->nullable();
Text::make('name')->rules(['string', 'max:256']);
```

Generated resource controllers automatically use the rules for `store` and
`update` actions:

```php
$validated = $request->validate((new UserSchema)->validationRules());
```

React and Vue adapters display Laravel validation errors returned through
Inertia. The Blade and Livewire adapters use their host framework error bags.

## Define a table

```php
use XaviWorks\ModularSchemaUi\Tables\Columns\BooleanColumn;
use XaviWorks\ModularSchemaUi\Tables\Columns\TextColumn;
use XaviWorks\ModularSchemaUi\Tables\Table;

public function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('name')->sortable()->searchable(),
            TextColumn::make('email')->searchable(),
            BooleanColumn::make('active')->sortable(),
        ])
        ->perPageOptions([10, 25, 50]);
}
```

The query pipeline provides:

- One global search across searchable columns
- Allowlisted sorting
- Allowlisted filters
- Pagination
- Per-page limits

Use the schema in a controller:

```php
use App\Modular\Users\UserSchema;
use Illuminate\Http\Request;
use XaviWorks\ModularSchemaUi\State\RequestState;

public function index(Request $request)
{
    $schema = new UserSchema;
    $definition = $schema->resolveTableDefinition();
    $state = RequestState::from(
        $request->all(),
        $definition->sortableColumnNames(),
        $definition->filterNames(),
    );

    return Inertia::render('users/index', [
        'table' => $schema->tablePayload(User::query()->toBase(), $state),
    ]);
}
```

## Filters

```php
use XaviWorks\ModularSchemaUi\Tables\Filters\BooleanFilter;
use XaviWorks\ModularSchemaUi\Tables\Filters\PresenceFilter;
use XaviWorks\ModularSchemaUi\Tables\Filters\SelectFilter;
use XaviWorks\ModularSchemaUi\Tables\Filters\TextFilter;

return $table->filters([
    SelectFilter::make('role')->options([
        'admin' => 'Administrator',
        'user' => 'User',
    ]),
    BooleanFilter::make('active'),
    PresenceFilter::make('email_verified_at'),
    TextFilter::make('department'),
]);
```

Only filter names declared by the table are accepted from the request.

## Row actions

```php
use XaviWorks\ModularSchemaUi\Tables\Action;

return $table->actions([
    Action::make('edit')->url('/users/{id}'),
    Action::make('delete')
        ->httpMethod('DELETE')
        ->url('/users/{id}')
        ->confirm('Delete this record?'),
]);
```

The `{id}` placeholder is replaced by the record ID. Actions are included in
the frontend-neutral table payload and rendered by the adapters.

## Render with React/Inertia

Install the adapter:

```bash
php artisan modular:install --frontend=react
```

Use the installed components in an Inertia page:

```tsx
import { ModularTable } from '@/components/modular/ModularTable';

export default function Users({ table }: { table: Parameters<typeof ModularTable>[0]['table'] }) {
    return <ModularTable table={table} />;
}
```

The installed React components are located at:

```text
resources/js/components/modular/
```

They use semantic `modular-*` CSS hooks and inherit the host application's
theme. The package does not force a color palette.

## Render with Blade

```blade
<x-modular-schema-ui::form
    :form="$form"
    action="{{ route('users.store') }}"
    method="POST"
/>

<x-modular-schema-ui::table :table="$table" />
```

## Vue and Livewire

Install the selected adapter:

```bash
php artisan modular:install --frontend=vue
php artisan modular:install --frontend=livewire
```

Both adapters consume the same frontend-neutral `form` and `table` payloads.
Use the installed files as the host application's starting point and connect
their submit/navigation handlers to the application's routes or Inertia
actions.

## Development checks

From the package repository:

```bash
composer validate --strict --no-check-publish
composer test
composer lint
composer analyse
```

The package workbench can be tested with:

```bash
cd workbench
PAO_DISABLE=true ./vendor/bin/pest --colors=never
```
