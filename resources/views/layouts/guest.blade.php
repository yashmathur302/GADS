<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <main class="min-h-screen flex flex-col items-center justify-center gap-8 px-4 py-12 bg-slate-950">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white/10 text-white ring-1 ring-white/20 mb-5">
                    <x-brand-mark class="w-8 h-8" />
                </div>

                <h1 class="sr-only">{{ config('app.name') }}</h1>

                <p
                    class="text-2xl sm:text-3xl font-bold tracking-tight text-white after:content-['|'] after:ml-0.5 after:animate-pulse after:font-light after:text-slate-400"
                    data-typewriter="{{ config('app.name') }}"
                    aria-hidden="true"
                ></p>

                <p class="mt-2 text-sm text-slate-400">{{ __('Secure admin access') }}</p>
            </div>

            <div class="w-full sm:max-w-md bg-white shadow-xl ring-1 ring-black/5 overflow-hidden sm:rounded-2xl px-6 py-8 sm:px-8">
                {{ $slot }}
            </div>
        </main>
    </body>
</html>
