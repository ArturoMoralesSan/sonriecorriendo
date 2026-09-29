<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- La aplicación utiliza exclusivamente el modo claro --}}
        <script>
            (function() {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');

                localStorage.setItem('appearance', 'light');
            })();
        </script>

        {{-- Fondo global permanente en modo claro --}}
        <style>
            html {
                background-color: #ffffff;
                color-scheme: light;
            }

            html.dark {
                background-color: #ffffff;
                color-scheme: light;
            }

            body {
                background-color: #ffffff;
            }
        </style>

        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
        <link rel="manifest" href="/site.webmanifest">

        @fonts

        @vite([
            'resources/css/app.css',
            'resources/js/app.ts',
            "resources/js/pages/{$page['component']}.vue"
        ])

        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>

    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>