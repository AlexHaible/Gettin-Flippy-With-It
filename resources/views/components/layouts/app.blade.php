<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cinema Companion</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poiret+One&family=Montserrat:wght@300;400;600&display=swap"
        rel="stylesheet">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
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

<body
    class="antialiased min-h-screen flex items-center justify-center overflow-x-hidden selection:bg-gold-500 selection:text-black">
    <header
        class="w-full h-14 fixed top-0 left-0 bg-noir-900/90 backdrop-blur-md border-b border-gold-900/30 z-50 px-4 flex items-center justify-between"
        x-data="{ mobileMenuOpen: false }">
        
        <a href="{{ route('dashboard') }}" class="text-gold-500 font-display font-bold tracking-tighter text-xl">
            PC
        </a>

        {{-- Desktop Navigation --}}
        <nav class="hidden md:flex items-center space-x-6">
            @auth
                <a href="{{ route('index') }}" class="text-gold-500 hover:text-gold-300 font-semibold text-sm uppercase tracking-widest transition-colors">Index</a>
                <a href="{{ route('dashboard') }}" class="text-gold-500 hover:text-gold-300 font-semibold text-sm uppercase tracking-widest transition-colors">Dashboard</a>
                <a href="{{ route('watchlist') }}" class="text-gold-500 hover:text-gold-300 font-semibold text-sm uppercase tracking-widest transition-colors">Watchlist</a>
                <a href="{{ route('bingo') }}" class="text-gold-500 hover:text-gold-300 font-semibold text-sm uppercase tracking-widest transition-colors">The Gauntlet</a>
                <a href="{{ route('logout') }}" class="text-gold-500 hover:text-gold-300 font-semibold text-sm uppercase tracking-widest transition-colors">Logout</a>
            @else
                <a href="{{ route('login') }}" class="text-gold-500 hover:text-gold-300 font-semibold text-sm uppercase tracking-widest transition-colors">Auth</a>
            @endauth
        </nav>

        {{-- Mobile Toggle --}}
        <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-gold-500 focus:outline-none p-2">
            <div class="relative w-6 h-6">
                {{-- Hamburger --}}
                <svg x-show="!mobileMenuOpen" class="w-6 h-6 absolute inset-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                {{-- Close --}}
                <svg x-show="mobileMenuOpen" class="w-6 h-6 absolute inset-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
        </button>

        {{-- Mobile Navigation --}}
        <div x-show="mobileMenuOpen" 
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             @click.away="mobileMenuOpen = false"
             class="absolute top-14 left-0 w-full bg-noir-900 border-b border-gold-900/30 md:hidden flex flex-col p-4 space-y-4 shadow-xl z-[100]">
            @auth
                <a href="{{ route('index') }}" class="text-gold-500 font-semibold uppercase tracking-widest py-2 border-b border-gold-900/10">Index</a>
                <a href="{{ route('dashboard') }}" class="text-gold-500 font-semibold uppercase tracking-widest py-2 border-b border-gold-900/10">Dashboard</a>
                <a href="{{ route('watchlist') }}" class="text-gold-500 font-semibold uppercase tracking-widest py-2 border-b border-gold-900/10">Watchlist</a>
                <a href="{{ route('bingo') }}" class="text-gold-500 font-semibold uppercase tracking-widest py-2 border-b border-gold-900/10">The Gauntlet</a>
                <a href="{{ route('logout') }}" class="text-gold-500 font-semibold uppercase tracking-widest py-2">Logout</a>
            @else
                <a href="{{ route('login') }}" class="text-gold-500 font-semibold uppercase tracking-widest py-2">Auth</a>
            @endauth
        </div>
    </header>
    <main class="w-full mx-auto pt-14">
        {{ $slot }}
    </main>
</body>

</html>