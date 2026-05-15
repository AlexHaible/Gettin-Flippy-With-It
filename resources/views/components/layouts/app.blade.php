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
        }

        /* Background pattern on a pseudo-element so backdrop-filter doesn't
           break position:fixed descendants (known browser behaviour). */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            z-index: -1;
            background-image: url('{{ Vite::asset('resources/images/background.jpg') }}');
            background-repeat: repeat;
            background-size: 612px;
            filter: brightness(0.3);
        }
    </style>
</head>

<body class="antialiased min-h-screen overflow-x-hidden selection:bg-gold-500 selection:text-black">

    {{-- ════════════════════════════════════════════
         HEADER — Alpine scope wraps button + dropdown together
         so click.away doesn't fire on the toggle button itself
    ════════════════════════════════════════════ --}}
    @php
        $topGenres = Cache::remember('nav_genres', 3600, function() {
            return \App\Models\Movie::select('genres')->get()
                ->flatMap(fn($m) => json_decode($m->genres, true) ?? [])
                ->countBy()
                ->sortDesc()
                ->take(8);
        });
        $topActors = Cache::remember('nav_actors', 3600, function() {
            return \App\Models\Movie::select('cast')->get()
                ->flatMap(fn($m) => json_decode($m->cast, true) ?? [])
                ->countBy()
                ->sortDesc()
                ->take(8);
        });
    @endphp

    <div x-data="{ open: false, libraryOpen: false }" class="fixed top-0 left-0 w-full z-[200]">
        <header class="w-full h-14 bg-noir-900/95 border-b border-gold-900/30 px-4 flex items-center justify-between shadow-lg">
            <div class="flex items-center space-x-8">
                {{-- Desktop Navigation --}}
                <nav class="hidden md:flex items-center space-x-6">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gold-500 hover:text-gold-300 font-semibold text-sm uppercase tracking-widest transition-colors">Dashboard</a>
                        
                        {{-- Library Dropdown --}}
                        <div class="relative" @mouseenter="libraryOpen = true" @mouseleave="libraryOpen = false">
                            <button class="text-gold-500 hover:text-gold-300 font-semibold text-sm uppercase tracking-widest transition-colors flex items-center gap-1 py-4">
                                Library
                                <svg class="w-4 h-4 transition-transform duration-200" :class="libraryOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            
                            <div x-show="libraryOpen" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="absolute top-full left-0 w-[400px] bg-noir-900 border border-gold-900/40 shadow-2xl rounded-b-lg p-6 grid grid-cols-2 gap-8"
                                 style="display:none;">
                                
                                <div>
                                    <p class="text-[10px] text-gold-700 uppercase tracking-[0.2em] mb-4 font-bold border-b border-gold-900/20 pb-1">Genres</p>
                                    <div class="space-y-2">
                                        @foreach($topGenres as $genre => $count)
                                            <a href="{{ route('genre', ['name' => $genre]) }}" class="block text-xs text-gold-400 hover:text-gold-200 transition-colors">{{ $genre }}</a>
                                        @endforeach
                                        <a href="{{ route('browse') }}" class="block text-[10px] text-gold-600 hover:text-gold-400 mt-4 italic">View All Genres →</a>
                                    </div>
                                </div>

                                <div>
                                    <p class="text-[10px] text-gold-700 uppercase tracking-[0.2em] mb-4 font-bold border-b border-gold-900/20 pb-1">Cast</p>
                                    <div class="space-y-2">
                                        @foreach($topActors as $actor => $count)
                                            <a href="{{ route('actor', ['name' => $actor]) }}" class="block text-xs text-gold-400 hover:text-gold-200 transition-colors truncate">{{ $actor }}</a>
                                        @endforeach
                                        <a href="{{ route('browse') }}" class="block text-[10px] text-gold-600 hover:text-gold-400 mt-4 italic">View All Cast →</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('index') }}" class="text-gold-500 hover:text-gold-300 font-semibold text-sm uppercase tracking-widest transition-colors">Calendar</a>
                        <a href="{{ route('watchlist') }}" class="text-gold-500 hover:text-gold-300 font-semibold text-sm uppercase tracking-widest transition-colors">Watchlist</a>
                        <a href="{{ route('bingo') }}" class="text-gold-500 hover:text-gold-300 font-semibold text-sm uppercase tracking-widest transition-colors">The Gauntlet</a>
                    @endauth
                </nav>
            </div>

            <div class="flex items-center space-x-4">
                @auth
                    <a href="{{ route('logout') }}" class="hidden md:block text-gold-500 hover:text-gold-300 font-semibold text-sm uppercase tracking-widest transition-colors">Logout</a>
                @else
                    <a href="{{ route('login') }}" class="text-gold-500 hover:text-gold-300 font-semibold text-sm uppercase tracking-widest transition-colors">Login</a>
                @endauth

                {{-- Mobile Toggle --}}
                <button @click="open = !open" class="md:hidden text-gold-500 focus:outline-none p-2 -mr-2">
                    <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </header>

        {{-- Mobile Dropdown --}}
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             @click.outside="open = false"
             class="w-full bg-noir-900/98 border-b border-gold-900/30 flex-col p-4 space-y-1 shadow-2xl overflow-y-auto max-h-[calc(100vh-3.5rem)]"
             style="display:none;"
             x-data="{ mobileLibraryOpen: false }">
            @auth
                <a href="{{ route('dashboard') }}" class="text-gold-500 font-semibold uppercase tracking-widest py-3 px-2 border-b border-gold-900/20 hover:text-gold-300 transition-colors block">Dashboard</a>
                <a href="{{ route('browse') }}" class="text-gold-500 font-semibold uppercase tracking-widest py-3 px-2 border-b border-gold-900/20 hover:text-gold-300 transition-colors block">Library</a>
                <a href="{{ route('index') }}" class="text-gold-500 font-semibold uppercase tracking-widest py-3 px-2 border-b border-gold-900/20 hover:text-gold-300 transition-colors block">Calendar</a>
                <a href="{{ route('watchlist') }}" class="text-gold-500 font-semibold uppercase tracking-widest py-3 px-2 border-b border-gold-900/20 hover:text-gold-300 transition-colors block">Watchlist</a>
                <a href="{{ route('bingo') }}" class="text-gold-500 font-semibold uppercase tracking-widest py-3 px-2 border-b border-gold-900/20 hover:text-gold-300 transition-colors block">The Gauntlet</a>
                <a href="{{ route('logout') }}" class="text-gold-500 font-semibold uppercase tracking-widest py-3 px-2 hover:text-gold-300 transition-colors block">Logout</a>
            @else
                <a href="{{ route('login') }}" class="text-gold-500 font-semibold uppercase tracking-widest py-3 px-2 hover:text-gold-300 transition-colors block">Login</a>
            @endauth
        </div>
    </div>

    <main class="w-full mx-auto pt-14">
        {{ $slot }}
    </main>

</body>

</html>