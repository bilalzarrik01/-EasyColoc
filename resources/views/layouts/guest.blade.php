<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="space-body font-sans antialiased">
        @php($wideAuth = request()->routeIs('login') || request()->routeIs('register'))
        <div class="min-h-screen flex flex-col items-center px-4 py-6 {{ $wideAuth ? 'justify-center' : 'sm:justify-center sm:pt-0' }}">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-indigo-100" />
                </a>
            </div>

            @if ($wideAuth)
                <div class="w-full max-w-6xl mt-4">
                    {{ $slot }}
                </div>
            @else
                <div class="w-full sm:max-w-md mt-6 px-6 py-4 space-panel auth-glow overflow-hidden sm:rounded-2xl">
                    {{ $slot }}
                </div>
            @endif
        </div>
    </body>
</html>
