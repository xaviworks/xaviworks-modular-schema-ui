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
        {--authorize : Add Laravel policy authorization calls to the generated controller}
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

        $controllerPath = app_path("Http/Controllers/{$controllerClass}.php");
        $controller = $this->addTableStateToController(
            $this->addValidationToController($filesToWrite[$controllerPath], $schemaClass),
            $schemaClass,
        );
        $filesToWrite[$controllerPath] = $this->option('authorize')
            ? $this->addAuthorizationToController($controller, class_basename($model))
            : $controller;

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
        $actionImports = ['use XaviWorks\\ModularSchemaUi\\Tables\\Action;'];

        foreach ($columns as $column) {
            $columnName = $column;
            $type = Schema::getColumnType($table, $columnName);

            if ($this->isSensitiveColumn($columnName) && ! str_contains(strtolower($columnName), 'password')) {
                continue;
            }

            $field = $this->fieldExpression($table, $columnName, $type, $fieldImports);
            $fieldLines[] = "            {$field},";

            if ($this->isSensitiveColumn($columnName)) {
                continue;
            }

            $column = var_export($columnName, true);
            $columnType = $type === 'boolean' ? 'BooleanColumn' : 'TextColumn';
            $columnImports[] = "use XaviWorks\\ModularSchemaUi\\Tables\\Columns\\{$columnType};";
            $columnLines[] = "            {$columnType}::make({$column})->sortable()->searchable(),";
            if ($type === 'boolean') {
                $filterImports[] = 'use XaviWorks\\ModularSchemaUi\\Tables\\Filters\\BooleanFilter;';
                $filterLines[] = "            BooleanFilter::make({$column}),";
            }

            if ($this->isNullableColumn($table, $columnName)) {
                $filterImports[] = 'use XaviWorks\\ModularSchemaUi\\Tables\\Filters\\PresenceFilter;';
                $filterLines[] = "            PresenceFilter::make({$column}),";
            }
        }

        $actionLines = [
            "            Action::make('edit')->url('/".Str::kebab(Str::pluralStudly(str_replace('Schema', '', $schemaClass)))."/{id}'),",
            "            Action::make('delete')->httpMethod('DELETE')->url('/".Str::kebab(Str::pluralStudly(str_replace('Schema', '', $schemaClass)))."/{id}')->confirm('Delete this record?'),",
        ];

        $fieldImports = array_values(array_unique($fieldImports));
        $columnImports = array_values(array_unique($columnImports));
        $filterImports = array_values(array_unique($filterImports));

        return "<?php\n\nnamespace App\\Modular\\".Str::pluralStudly(str_replace('Schema', '', $schemaClass)).";\n\n".
            implode("\n", $fieldImports)."\n".implode("\n", $columnImports)."\n".implode("\n", $filterImports)."\n".
            implode("\n", $actionImports)."\n".
            "use XaviWorks\\ModularSchemaUi\\Resources\\ResourceSchema;\n\n".
            "final class {$schemaClass} extends ResourceSchema\n{\n".
            "    public function form(Form \$form): Form\n    {\n        return \$form->fields([\n".
            implode("\n", $fieldLines)."\n        ]);\n    }\n\n".
            "    public function table(Table \$table): Table\n    {\n        return \$table->columns([\n".
            implode("\n", $columnLines)."\n        ])->filters([\n".implode("\n", $filterLines)."\n        ])->actions([\n".implode("\n", $actionLines)."\n        ]);\n    }\n}\n";
    }

    /** @param list<string> $imports */
    private function fieldExpression(string $table, string $column, string $type, array &$imports): string
    {
        $class = match ($type) {
            'text', 'mediumText', 'longText' => 'Textarea',
            'boolean' => 'Select',
            'date' => 'Date',
            'datetime', 'datetimetz', 'timestamp' => 'DateTime',
            'integer', 'bigint', 'smallint', 'decimal', 'double', 'float' => 'Number',
            default => str_contains(strtolower($column), 'email') ? 'Email' : (str_contains(strtolower($column), 'password') ? 'Password' : 'Text'),
        };

        $imports[] = "use XaviWorks\\ModularSchemaUi\\Forms\\Fields\\{$class};";
        $expression = "{$class}::make(".var_export($column, true).')';

        if ($class === 'Select') {
            $expression .= "->options([1 => 'Yes', 0 => 'No'])";
        }

        if (! $this->isNullableColumn($table, $column)) {
            $expression .= '->required()';
        }

        $length = $this->columnLength($table, $column);

        if ($length !== null) {
            $expression .= "->maxLength({$length})";
        }

        if ($class === 'Password') {
            $expression .= '->minLength(8)';
        }

        return $expression;
    }

    private function columnLength(string $table, string $column): ?int
    {
        foreach (Schema::getColumns($table) as $details) {
            if (($details['name'] ?? null) === $column && is_int($details['length'] ?? null)) {
                return $details['length'];
            }
        }

        return null;
    }

    private function isSensitiveColumn(string $column): bool
    {
        return str_contains(strtolower($column), 'password')
            || str_contains(strtolower($column), 'secret')
            || str_contains(strtolower($column), 'recovery')
            || str_contains(strtolower($column), 'token')
            || str_starts_with(strtolower($column), 'two_factor');
    }

    private function isNullableColumn(string $table, string $column): bool
    {
        foreach (Schema::getColumns($table) as $details) {
            if (($details['name'] ?? null) === $column) {
                return (bool) ($details['nullable'] ?? false);
            }
        }

        return false;
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

    private function addValidationToController(string $source, string $schemaClass): string
    {
        $source = preg_replace_callback(
            '/(?<indent>\s+)(?<model>[A-Za-z0-9_]+)::query\(\)->create\(\$request->only\((?<columns>.*?)\)\);/s',
            fn (array $match): string => $match['indent']
                .'$validated = $request->validate((new '.$schemaClass.')->validationRules());'."\n"
                .$match['indent'].$match['model'].'::query()->create($validated);',
            $source,
        ) ?? $source;

        return preg_replace_callback(
            '/(?<indent>\s+)\$(?<model>[A-Za-z0-9_]+)->update\(\$request->only\((?<columns>.*?)\)\);/s',
            fn (array $match): string => $match['indent']
                .'$validated = $request->validate((new '.$schemaClass.')->validationRules());'."\n"
                .$match['indent'].'$'.$match['model'].'->update($validated);',
            $source,
        ) ?? $source;
    }

    private function addTableStateToController(string $source, string $schemaClass): string
    {
        $source = preg_replace(
            '/RequestState::from\(\$request->all\(\), .*?\)\)/s',
            '$state)',
            $source,
            1,
        ) ?? $source;

        $source = str_replace('$state));', '$state);', $source);

        return preg_replace(
            '/(?<indent>\s+)\$schema = new '.preg_quote($schemaClass, '/').';/',
            '$1$schema = new '.$schemaClass.";\n".
                '$1$tableDefinition = $schema->resolveTableDefinition();'."\n".
                '$1$state = RequestState::from($request->all(), $tableDefinition->sortableColumnNames(), $tableDefinition->filterNames());',
            $source,
            1,
        ) ?? $source;
    }

    private function addAuthorizationToController(string $source, string $model): string
    {
        $source = str_replace(
            "    public function create(): Response\n    {\n",
            "    public function create(): Response\n    {\n        \$this->authorize('create', {$model}::class);\n",
            $source,
        );

        $source = preg_replace(
            '/(?<method>public function store\([^\n]+\): RedirectResponse)(?<body>\n    \{)/',
            '$1$2'."\n        ".'$this->authorize(\'create\', '.$model.'::class);',
            $source,
        ) ?? $source;

        return preg_replace_callback(
            '/(?<method>public function (?:edit|update|destroy)\([^\n]+\)(?:\: Response|\: RedirectResponse))(?<body>\n    \{)/',
            fn (array $match): string => $match['method'].$match['body']."\n        \$this->authorize('".
                (str_contains($match['method'], 'destroy') ? 'delete' : 'update')."', \$".
                Str::camel($model).');',
            $source,
        ) ?? $source;
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
