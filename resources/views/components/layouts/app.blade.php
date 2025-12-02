<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Cinema Companion' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poiret+One&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background-color: var(--color-noir-950);
            color: var(--color-gold-100);
            font-family: var(--font-serif);

            background-image: url('{{ Vite::asset('resources/images/background.jpg') }}');
            background-repeat: repeat;
            background-size: 612px;
            backdrop-filter: brightness(0.3);
        }
    </style>
</head>
<body class="antialiased min-h-screen flex items-center justify-center overflow-x-hidden selection:bg-gold-500 selection:text-black">
    <header class="w-full h-[40px] fixed top-0 left-0 flex items-center justify-end bg-noir-900/75 backdrop-blur-md shadow-md z-20">
        <a class="absolute right-4">
        @auth
            <a href="{{ route('index') }}" class="deco-text-metallic text-gold-500 hover:text-gold-300 font-semibold mx-2">Index</a>
            <a href="{{ route('dashboard') }}" class="deco-text-metallic text-gold-500 hover:text-gold-300 font-semibold mx-2">Dashboard</a>
            <a href="{{ route('logout') }}" class="deco-text-metallic text-gold-500 hover:text-gold-300 font-semibold mx-2">Logout</a>
        @else
            <a href="{{ route('login') }}" class="deco-text-metallic text-gold-500 hover:text-gold-300 font-semibold mx-2">Auth</a>
        @endauth
        </nav>
    </header>
    <main class="w-full max-w-lg mx-auto">
        {{ $slot }}
    </main>
</body>
</html>
