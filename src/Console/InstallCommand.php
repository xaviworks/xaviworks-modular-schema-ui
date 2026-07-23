<?php

namespace XaviWorks\ModularSchemaUi\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

final class InstallCommand extends Command
{
    protected $signature = 'modular:install
        {--frontend= : Frontend adapter: blade, react, vue, or livewire}
        {--force : Replace existing adapter files}
        {--dry-run : Show the selected adapter without writing files}';

    protected $description = 'Install the Modular Schema UI frontend adapter for this Laravel application';

    public function handle(Filesystem $files): int
    {
        $frontend = $this->option('frontend') ?: $this->detectFrontend($files);

        if (! in_array($frontend, ['blade', 'react', 'vue', 'livewire'], true)) {
            $this->error('Unsupported frontend. Choose blade, react, vue, or livewire.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->line('<fg=bright-blue;options=bold>XaviWorks Modular Schema UI</>');
        $this->line('Created by Junn Xavier Adalid');
        $this->line('A frontend-neutral Laravel toolkit for reusable forms, tables, filters, actions, and validation.');
        $this->newLine();
        $this->info("Selected Modular frontend: {$frontend}");

        if (! $this->option('dry-run')
            && ! $this->option('no-interaction')
            && ! $this->confirm("Install the {$frontend} Modular adapter?", true)) {
            $this->warn('Modular adapter installation cancelled.');

            return self::SUCCESS;
        }

        if ($frontend === 'blade') {
            $this->line('Blade support is provided by the package; no application files are required.');

            return self::SUCCESS;
        }

        $source = dirname(__DIR__, 2)."/stubs/frontend/{$frontend}";
        $destination = $this->destination($frontend);

        if ($this->option('dry-run')) {
            $this->line("Would install adapter files into {$destination}");

            return self::SUCCESS;
        }

        foreach ($files->allFiles($source) as $file) {
            $relativePath = str_replace($source.'/', '', $file->getPathname());
            $target = $destination.'/'.str_replace('.stub', '', $relativePath);

            if ($files->exists($target) && ! $this->option('force')) {
                $this->warn("Skipped existing file: {$target} (use --force to replace)");

                continue;
            }

            $files->ensureDirectoryExists(dirname($target));
            $files->copy($file->getPathname(), $target);
            $this->line("Installed: {$target}");
        }

        return self::SUCCESS;
    }

    private function detectFrontend(Filesystem $files): string
    {
        $package = $this->readJson($files, base_path('package.json'));
        $composer = $this->readJson($files, base_path('composer.json'));
        $npmDependencies = [...($package['dependencies'] ?? []), ...($package['devDependencies'] ?? [])];
        $composerPackages = [...($composer['require'] ?? []), ...($composer['require-dev'] ?? [])];

        if (isset($npmDependencies['react'], $npmDependencies['@inertiajs/react'])) {
            return 'react';
        }

        if (isset($npmDependencies['vue'], $npmDependencies['@inertiajs/vue3'])) {
            return 'vue';
        }

        if (isset($composerPackages['livewire/livewire'])) {
            return 'livewire';
        }

        return 'blade';
    }

    /** @return array<string, mixed> */
    private function readJson(Filesystem $files, string $path): array
    {
        if (! $files->exists($path)) {
            return [];
        }

        $decoded = json_decode($files->get($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function destination(string $frontend): string
    {
        return match ($frontend) {
            'react', 'vue' => resource_path('js/components/modular'),
            'livewire' => resource_path('views/components/modular'),
            default => resource_path('views/vendor/modular-schema-ui'),
        };
    }
}
