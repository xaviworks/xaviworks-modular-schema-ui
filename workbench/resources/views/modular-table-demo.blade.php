<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Modular Table | {{ config('app.name') }}</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-slate-100 p-6 text-slate-900">
        <main class="mx-auto max-w-4xl rounded-xl bg-white p-8 shadow-sm">
            <p class="mb-2 text-sm font-semibold uppercase tracking-wide text-indigo-600">XaviWorks Modular UI</p>
            <h1 class="mb-2 text-2xl font-semibold">Users</h1>
            <p class="mb-6 text-slate-600">A first modular table rendered from PHP definitions.</p>

            <x-modular-schema-ui::table :table="$table" />
        </main>
    </body>
</html>
