<?php

namespace XaviWorks\ModularSchemaUi\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class CreateCommand extends Command
{
    protected $signature = 'modular:create
        {name : The singular resource name, for example User}
        {--model= : The Eloquent model class, defaults to App\\Models\\{name}}
        {--table= : The database table, defaults to the plural snake-case resource name}
        {--frontend= : Frontend adapter: blade, react, vue, or livewire}
        {--force : Replace existing generated files}';

    protected $description = 'Create a modular resource from an existing Eloquent model and database table';

    public function handle(Filesystem $files): int
    {
        $name = Str::studly((string) $this->argument('name'));
        $model = (string) ($this->option('model') ?: "App\\Models\\{$name}");
        $table = (string) ($this->option('table') ?: Str::snake(Str::pluralStudly($name)));
        $frontend = (string) ($this->option('frontend') ?: $this->detectFrontend($files));

        if (! class_exists($model) || ! is_subclass_of($model, Model::class)) {
            $this->error("The model {$model} does not exist or is not an Eloquent model.");

            return self::FAILURE;
        }

        if (! Schema::hasTable($table)) {
            $this->error("The database table {$table} does not exist. Run the migrations first.");

            return self::FAILURE;
        }

        if (! in_array($frontend, ['blade', 'react', 'vue', 'livewire'], true)) {
            $this->error('Unsupported frontend. Choose blade, react, vue, or livewire.');

            return self::FAILURE;
        }

        $columns = array_values(array_filter(
            Schema::getColumnListing($table),
            fn (string $column): bool => ! in_array($column, ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token'], true),
        ));

        if ($columns === []) {
            $this->error("No usable columns were found in {$table}.");

            return self::FAILURE;
        }

        $safeColumns = array_values(array_filter(
            $columns,
            fn (string $column): bool => ! $this->isSensitiveColumn($column)
                || str_contains(strtolower($column), 'password'),
        ));

        $resource = Str::pluralStudly($name);
        $resourceSlug = Str::kebab($resource);
        $schemaClass = "{$name}Schema";
        $controllerClass = "{$name}Controller";

        $filesToWrite = [
            app_path("Modular/{$resource}/{$schemaClass}.php") => $this->schemaCode($schemaClass, $model, $table, $safeColumns),
            app_path("Http/Controllers/{$controllerClass}.php") => $this->controllerCode($schemaClass, $controllerClass, $model, $resourceSlug, $safeColumns),
            base_path('routes/modular.php') => $this->routeCode($controllerClass, $resourceSlug),
        ];

        if ($frontend === 'react') {
            $filesToWrite[resource_path("js/pages/{$resourceSlug}/index.tsx")] = $this->reactIndexCode($resource);
            $filesToWrite[resource_path("js/pages/{$resourceSlug}/create.tsx")] = $this->reactFormPageCode($resource, 'create');
            $filesToWrite[resource_path("js/pages/{$resourceSlug}/edit.tsx")] = $this->reactFormPageCode($resource, 'edit');
        }

        foreach ($filesToWrite as $path => $contents) {
            if ($files->exists($path) && ! $this->option('force')) {
                $this->warn("Skipped existing file: {$path} (use --force to replace)");

                continue;
            }

            $files->ensureDirectoryExists(dirname($path));
            $files->put($path, $contents);
            $this->line("Created: {$path}");
        }

        $this->appendRouteLoader($files);
        $this->newLine();
        $this->info("Modular {$name} resource created from {$table}.");
        $this->line("Review the generated schema in app/Modular/{$resource}/{$schemaClass}.php before using it in production.");

        return self::SUCCESS;
    }

    /** @param list<string> $columns */
    private function schemaCode(string $schemaClass, string $model, string $table, array $columns): string
    {
        $fieldImports = ['use XaviWorks\\ModularSchemaUi\\Forms\\Form;'];
        $fieldLines = [];
        $columnImports = ['use XaviWorks\\ModularSchemaUi\\Tables\\Table;'];
        $columnLines = [];
        $filterImports = [];
        $filterLines = [];

        foreach ($columns as $column) {
            $type = Schema::getColumnType($table, $column);

            if ($this->isSensitiveColumn($column) && ! str_contains(strtolower($column), 'password')) {
                continue;
            }

            $field = $this->fieldExpression($column, $type, $fieldImports);
            $fieldLines[] = "            {$field},";

            if ($this->isSensitiveColumn($column)) {
                continue;
            }

            $column = var_export($column, true);
            $columnType = $type === 'boolean' ? 'BooleanColumn' : 'TextColumn';
            $columnImports[] = "use XaviWorks\\ModularSchemaUi\\Tables\\Columns\\{$columnType};";
            $columnLines[] = "            {$columnType}::make({$column})->sortable()->searchable(),";
            if ($type === 'boolean') {
                $filterImports[] = 'use XaviWorks\\ModularSchemaUi\\Tables\\Filters\\BooleanFilter;';
                $filterLines[] = "            BooleanFilter::make({$column}),";
            }

            if (str_ends_with(strtolower(trim($column, "'")), '_verified_at')) {
                $filterImports[] = 'use XaviWorks\\ModularSchemaUi\\Tables\\Filters\\PresenceFilter;';
                $filterLines[] = "            PresenceFilter::make({$column}),";
            }
        }

        $fieldImports = array_values(array_unique($fieldImports));
        $columnImports = array_values(array_unique($columnImports));
        $filterImports = array_values(array_unique($filterImports));

        return "<?php\n\nnamespace App\\Modular\\".Str::pluralStudly(str_replace('Schema', '', $schemaClass)).";\n\n".
            implode("\n", $fieldImports)."\n".implode("\n", $columnImports)."\n".implode("\n", $filterImports)."\n".
            "use XaviWorks\\ModularSchemaUi\\Resources\\ResourceSchema;\n\n".
            "final class {$schemaClass} extends ResourceSchema\n{\n".
            "    public function form(Form \$form): Form\n    {\n        return \$form->fields([\n".
            implode("\n", $fieldLines)."\n        ]);\n    }\n\n".
            "    public function table(Table \$table): Table\n    {\n        return \$table->columns([\n".
            implode("\n", $columnLines)."\n        ])->filters([\n".implode("\n", $filterLines)."\n        ]);\n    }\n}\n";
    }

    /** @param list<string> $imports */
    private function fieldExpression(string $column, string $type, array &$imports): string
    {
        $class = match ($type) {
            'text', 'mediumText', 'longText' => 'Textarea',
            'boolean' => 'Select',
            default => in_array($type, ['date', 'datetime', 'datetimetz', 'timestamp'], true)
                ? 'Text'
                : (str_contains(strtolower($column), 'email') ? 'Email' : (str_contains(strtolower($column), 'password') ? 'Password' : 'Text')),
        };

        $imports[] = "use XaviWorks\\ModularSchemaUi\\Forms\\Fields\\{$class};";
        $expression = "{$class}::make(".var_export($column, true).')';

        if ($class === 'Select') {
            $expression .= "->options([1 => 'Yes', 0 => 'No'])";
        }

        return $expression;
    }

    private function isSensitiveColumn(string $column): bool
    {
        return str_contains(strtolower($column), 'password')
            || str_contains(strtolower($column), 'secret')
            || str_contains(strtolower($column), 'recovery')
            || str_contains(strtolower($column), 'token')
            || str_starts_with(strtolower($column), 'two_factor');
    }

    /** @param list<string> $columns */
    private function controllerCode(string $schemaClass, string $controllerClass, string $model, string $resourceSlug, array $columns): string
    {
        $modelShort = class_basename($model);
        $variable = Str::camel($modelShort);
        $columnList = var_export($columns, true);

        return "<?php\n\nnamespace App\\Http\\Controllers;\n\nuse {$model};\nuse App\\Modular\\".Str::pluralStudly($modelShort)."\\{$schemaClass};\nuse Illuminate\\Http\\RedirectResponse;\nuse Illuminate\\Http\\Request;\nuse Inertia\\Inertia;\nuse Inertia\\Response;\nuse XaviWorks\\ModularSchemaUi\\Forms\\Form;\nuse XaviWorks\\ModularSchemaUi\\State\\RequestState;\n\nfinal class {$controllerClass} extends Controller\n{\n    public function index(Request \$request): Response\n    {\n        \$schema = new {$schemaClass};\n        \$table = \$schema->tablePayload({$modelShort}::query()->toBase(), RequestState::from(\$request->all(), {$columnList}));\n\n        return Inertia::render('{$resourceSlug}/index', ['table' => \$table]);\n    }\n\n    public function create(): Response\n    {\n        return Inertia::render('{$resourceSlug}/create', ['form' => (new {$schemaClass})->formPayload()]);\n    }\n\n    public function store(Request \$request): RedirectResponse\n    {\n        {$modelShort}::query()->create(\$request->only({$columnList}));\n\n        return to_route('{$resourceSlug}.index');\n    }\n\n    public function edit({$modelShort} \${$variable}): Response\n    {\n        return Inertia::render('{$resourceSlug}/edit', [\n            'form' => (new {$schemaClass})->form(Form::make()->model(\${$variable}))->toArray(),\n            '{$variable}' => \${$variable},\n        ]);\n    }\n\n    public function update(Request \$request, {$modelShort} \${$variable}): RedirectResponse\n    {\n        \${$variable}->update(\$request->only({$columnList}));\n\n        return to_route('{$resourceSlug}.index');\n    }\n\n    public function destroy({$modelShort} \${$variable}): RedirectResponse\n    {\n        \${$variable}->delete();\n\n        return to_route('{$resourceSlug}.index');\n    }\n}\n";
    }

    private function routeCode(string $controllerClass, string $resourceSlug): string
    {
        return "<?php\n\nuse App\\Http\\Controllers\\{$controllerClass};\nuse Illuminate\\Support\\Facades\\Route;\n\nRoute::resource('{$resourceSlug}', {$controllerClass}::class)->except(['show']);\n";
    }

    private function reactIndexCode(string $resource): string
    {
        return "import { Head, Link } from '@inertiajs/react';\nimport { ModularTable } from '@/components/modular/ModularTable';\n\nexport default function Index({ table }: { table: Parameters<typeof ModularTable>[0]['table'] }) {\n    return <><Head title=\"{$resource}\" /><div><Link href=\"/".Str::kebab(Str::pluralStudly($resource))."/create\">Create {$resource}</Link><ModularTable table={table} /></div></>;\n}\n";
    }

    private function reactFormPageCode(string $resource, string $mode): string
    {
        $resourceSlug = Str::kebab(Str::pluralStudly($resource));
        $resourceVariable = $this->resourceVariable($resource);
        $props = $mode === 'edit' ? ", {$resourceVariable}" : '';
        $url = $mode === 'edit' ? "'/{$resourceSlug}/' + {$resourceVariable}?.id" : "'/{$resourceSlug}'";
        $method = $mode === 'edit' ? 'put' : 'post';
        $typeProps = $mode === 'edit' ? "; {$resourceVariable}: { id: number }" : '';

        return "import { Head, router } from '@inertiajs/react';\nimport { ModularForm } from '@/components/modular/ModularForm';\n\nexport default function ".Str::studly($mode)."({ form{$props} }: { form: Parameters<typeof ModularForm>[0]['form']{$typeProps} }) {\n    return <><Head title=\"{$mode} {$resource}\" /><ModularForm form={form} onSubmit={(event) => { event.preventDefault(); router.{$method}({$url}, Object.fromEntries(new FormData(event.currentTarget))); }} /></>;\n}\n";
    }

    private function resourceVariable(string $resource): string
    {
        return Str::camel(Str::singular($resource));
    }

    private function appendRouteLoader(Filesystem $files): void
    {
        $webRoutes = base_path('routes/web.php');
        $loader = "require __DIR__.'/modular.php';";

        if ($files->exists($webRoutes) && ! str_contains($files->get($webRoutes), $loader)) {
            $files->append($webRoutes, "\n{$loader}\n");
        }
    }

    private function detectFrontend(Filesystem $files): string
    {
        $package = $this->readJson($files, base_path('package.json'));
        $composer = $this->readJson($files, base_path('composer.json'));
        $npm = [...($package['dependencies'] ?? []), ...($package['devDependencies'] ?? [])];
        $php = [...($composer['require'] ?? []), ...($composer['require-dev'] ?? [])];

        return isset($npm['react'], $npm['@inertiajs/react']) ? 'react'
            : (isset($npm['vue'], $npm['@inertiajs/vue3']) ? 'vue'
                : (isset($php['livewire/livewire']) ? 'livewire' : 'blade'));
    }

    /** @return array<string, mixed> */
    private function readJson(Filesystem $files, string $path): array
    {
        if (! $files->exists($path)) {
            return [];
        }

        $value = json_decode($files->get($path), true);

        return is_array($value) ? $value : [];
    }
}
