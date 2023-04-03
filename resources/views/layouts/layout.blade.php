<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Gettin' Flippy With It!</title>

    <link href="https://fonts.bunny.net/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url('/') }}" />
    <meta property="og:title" content="Gettin' Flippy With It!" />
    <meta property="og:description"
        content="Literally just a single purpose application to determine who pays popcorn and sodas next time." />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:creator" content="@sauravisus" />

    <!-- Styles -->
    @vite('resources/css/app.css')
    @livewireStyles

    <!-- Scripts -->
    @vite('resources/js/app.js')
</head>

<body class="h-full">
    <nav class="w-full absolute top-0 flex justify-end pt-2">
        @auth
            <a href="/logout" class="px-4 py-2 rounded-md bg-slate-200 mr-2">Logout</a>
        @endauth
    </nav>
    <div class="relative flex flex-col justify-center h-full px-4 py-12 sm:px-6 lg:px-8">
        @yield('content')
    </div>

    @livewireScripts
</body>

</html>
