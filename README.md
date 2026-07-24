# XaviWorks Modular Schema UI

Frontend-neutral modular forms and tables for Laravel applications.

Define fields, columns, filters, sorting, searching, and pagination once in
Laravel. Render the resulting payload with Blade, React/Inertia, Vue/Inertia,
Livewire, or your own frontend.

Maintained by Junn Xavier Adalid under the XaviWorks developer name.

## Status

This package is currently in active development. The shared Laravel schema
API and Blade adapter are available, and the installer includes starter
adapters for React, Vue, and Livewire.

## Installation

Install the package:

```bash
composer require xaviworks/laravel-modular-schema-ui
php artisan modular:install --frontend=react
```

Supported adapters are `blade`, `react`, `vue`, and `livewire`. If no adapter
is specified, the installer detects React/Inertia, Vue/Inertia, or Livewire
from the host application and falls back to Blade.

## Define a modular schema

```php
use XaviWorks\ModularSchemaUi\Forms\Form;
use XaviWorks\ModularSchemaUi\Forms\Fields\Email;
use XaviWorks\ModularSchemaUi\Forms\Fields\Text;
use XaviWorks\ModularSchemaUi\Resources\ResourceSchema;

final class UserSchema extends ResourceSchema
{
    public function form(Form $form): Form
    {
        return $form->fields(
            Text::make('name')->required()->maxLength(256),
            Email::make('email')->required(),
        );
    }
}
```

Array declarations remain supported. Validation is declared beside each
modular field. Use `required()`, `maxLength()`, `minLength()`, `nullable()`, or
`rules()` for additional Laravel validation rules:

```php
Text::make('name')->required()->maxLength(256),
Email::make('email')->required(),
Password::make('password')->required()->minLength(8),
Text::make('nickname')->nullable()->rules(['string', 'max:80']),
```

The schema exposes the complete rule map through `validationRules()` and
normalized frontend metadata through each field payload. Laravel `FormRequest`
classes remain the authority for complex business validation and authorization.

## Multiple forms and tables

Use `UiSchema` when one feature has multiple screens:

```php
use XaviWorks\ModularSchemaUi\Forms\Form;
use XaviWorks\ModularSchemaUi\Forms\Fields\Date;
use XaviWorks\ModularSchemaUi\Forms\Fields\Select;
use XaviWorks\ModularSchemaUi\Resources\UiSchema;
use XaviWorks\ModularSchemaUi\Tables\Columns\TextColumn;
use XaviWorks\ModularSchemaUi\Tables\Table;

final class ReservationUi extends UiSchema
{
    public function createForm(Form $form): Form
    {
        return $form->fields(
            Select::make('room_id')->required(),
            Date::make('check_in')->required(),
            Date::make('check_out')->required(),
        );
    }

    public function archiveTable(Table $table): Table
    {
        return $table->columns(
            TextColumn::make('reference')->searchable(),
            TextColumn::make('archived_at'),
        );
    }
}
```

Resolve named definitions with `resolveForm('create')` and
`resolveTable('archive')`. This keeps multiple screens grouped by feature
without requiring a separate class for every small form or table.

## Table actions and authorization

Tables can expose frontend-neutral row actions. The `{id}` placeholder is
replaced by the record key by the adapters:

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

Generated resources include Edit and Delete actions automatically. Add
`--authorize` when creating a resource to add Laravel policy calls for create,
update, and delete.

## Use it with React/Inertia

```php
use Inertia\Inertia;

return Inertia::render('Users/Create', [
    'form' => (new UserSchema)->formPayload(),
]);
```

The installer places the React components in:

```text
resources/js/components/modular/
```

## Use it with Blade

```blade
<x-modular-schema-ui::form :form="$form" action="{{ route('users.store') }}" />
```

For complete usage, generated resources, fields, filters, actions, validation,
and frontend adapter examples, see [docs/USAGE.md](docs/USAGE.md).

## Package architecture

```text
src/Forms       Form and field definitions
src/Tables      Table, column, and filter definitions
src/Resources   Resource and feature-level UI schemas
src/Query       Focused search, filter, sort, and pagination services
src/State       Normalized request state
src/Support     Frontend-neutral schema payloads
src/View        Blade adapter
stubs/frontend  React, Vue, and Livewire starter adapters
workbench       Laravel integration application
```

## Development

```bash
composer install
composer test
composer lint
composer validate --strict --no-check-publish
```

The workbench is for package development and is excluded from package
archives. It is not required by applications installing the package.

## License

The MIT License. See [LICENSE](LICENSE).
