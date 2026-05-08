<x-layouts.app>
    @if(isset($heroBackdrop) && $heroBackdrop)
    <style>
        body {
            background-image: url('{{ $heroBackdrop }}') !important;
            background-size: cover !important;
            background-position: center !important;
            background-attachment: fixed !important;
            backdrop-filter: brightness(0.15) !important;
        }
    </style>
    @endif

    <div class="container mx-auto px-4 py-12 max-w-4xl">
        <div class="text-center mb-16 animate-fade-in-up">
            <h1 class="text-6xl font-display font-bold text-gold-500 text-shadow-gold uppercase tracking-[0.3em] mb-4">
                Wrapped {{ $year }}
            </h1>
            <p class="text-xl text-gold-300 font-serif tracking-widest">Your Cinematic Journey</p>
            
            @if(isset($availableYears) && $availableYears->count() > 1)
            <div class="flex flex-wrap justify-center gap-2 mt-6">
                @foreach($availableYears as $avYear)
                    <a href="{{ route('wrapped', ['year' => $avYear]) }}" 
                       class="px-4 py-1 rounded-full text-sm font-bold tracking-widest transition-all
                       {{ $year == $avYear ? 'bg-gold-500 text-noir-900 shadow-[0_0_10px_var(--color-gold-500)]' : 'bg-noir-900/50 text-gold-400 border border-gold-600/50 hover:border-gold-400 hover:text-gold-200 backdrop-blur-sm' }}">
                        {{ $avYear }}
                    </a>
                @endforeach
            </div>
            @endif
            
            <div class="deco-divider w-64 mx-auto mt-8"></div>
        </div>

        @if(!$hasData)
            <div class="bg-noir-900/90 deco-border-metallic border-2 rounded-lg p-12 text-center shadow-lg shadow-gold-900/20">
                <p class="text-2xl text-gold-500 font-display">No movies recorded in {{ $year }}.</p>
                <a href="{{ route('dashboard') }}" class="mt-6 inline-block text-gold-300 hover:text-gold-100 transition-colors border-b border-gold-600 pb-1">Return to Dashboard</a>
            </div>
        @else
            <!-- Key Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                <div class="bg-noir-900/90 deco-border-metallic border-2 rounded-lg p-8 shadow-[0_0_15px_var(--color-gold-900)] transform hover:scale-105 transition-transform duration-300">
                    <p class="text-gold-600 text-sm uppercase tracking-widest mb-2 font-bold">Total Showings</p>
                    <p class="text-6xl font-display text-gold-400 font-bold mb-2">{{ $totalMovies }}</p>
                    <p class="text-gold-700">Times you visited the cinema this year.</p>
                </div>

                <div class="bg-noir-900/90 deco-border-metallic border-2 rounded-lg p-8 shadow-[0_0_15px_var(--color-gold-900)] transform hover:scale-105 transition-transform duration-300">
                    <p class="text-gold-600 text-sm uppercase tracking-widest mb-2 font-bold">Total Time</p>
                    <p class="text-6xl font-display text-gold-400 font-bold mb-2">{{ $totalHours }} <span class="text-3xl">hrs</span></p>
                    <p class="text-gold-700">Immersed in another world.</p>
                </div>
            </div>

            <!-- Highlights -->
            <div class="space-y-8 mb-12">
                @if($longestMovie)
                <div class="bg-gradient-to-r from-noir-950 to-noir-900 border border-gold-800 rounded-lg p-8 flex flex-col md:flex-row items-center justify-between shadow-xl relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gold-900/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="mb-6 md:mb-0 z-10 relative">
                        <p class="text-gold-600 text-sm uppercase tracking-widest mb-2 font-bold">Longest Epic</p>
                        <h3 class="text-3xl font-display text-gold-100 font-bold">{{ $longestMovie->movie->title }}</h3>
                        <p class="text-gold-500 mt-2 text-lg">{{ $longestMovie->movie->runtime }} minutes</p>
                    </div>
                    @if($longestMovie->movie->poster_path)
                        <img src="https://image.tmdb.org/t/p/w200{{ $longestMovie->movie->poster_path }}" class="w-24 h-36 object-cover rounded shadow-lg shadow-black rotate-3 group-hover:rotate-6 transition-transform z-10 relative">
                    @endif
                </div>
                @endif

                @if($mostExpensive)
                <div class="bg-gradient-to-r from-noir-900 to-noir-950 border border-gold-800 rounded-lg p-8 flex flex-col md:flex-row items-center justify-between shadow-xl relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gold-900/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="mb-6 md:mb-0 z-10 relative">
                        <p class="text-gold-600 text-sm uppercase tracking-widest mb-2 font-bold">Premium Experience</p>
                        <h3 class="text-3xl font-display text-gold-100 font-bold">{{ $mostExpensive->movie->title }}</h3>
                        <p class="text-gold-500 mt-2 text-lg">{{ number_format($mostExpensive->price_total, 0, ',', '.') }} kr. at {{ $mostExpensive->cinema->name }}</p>
                    </div>
                    @if($mostExpensive->movie->poster_path)
                        <img src="https://image.tmdb.org/t/p/w200{{ $mostExpensive->movie->poster_path }}" class="w-24 h-36 object-cover rounded shadow-lg shadow-black -rotate-3 group-hover:-rotate-6 transition-transform z-10 relative">
                    @endif
                </div>
                @endif

                @if($biggestDisagreement)
                <div class="bg-gradient-to-r from-noir-950 to-red-950/30 border border-gold-800 rounded-lg p-8 flex flex-col md:flex-row items-center justify-between shadow-xl relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gold-900/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="mb-6 md:mb-0 z-10 relative w-full">
                        <p class="text-gold-600 text-sm uppercase tracking-widest mb-2 font-bold">Biggest Disagreement</p>
                        <h3 class="text-3xl font-display text-gold-100 font-bold">{{ $biggestDisagreement->movie->title }}</h3>
                        
                        <div class="mt-4 flex gap-6">
                            @foreach(['Alex' => 1, 'Casper' => 2] as $name => $uid)
                                @php
                                    $rating = $biggestDisagreement->ratings->firstWhere('user_id', $uid);
                                @endphp
                                <div>
                                    <p class="text-gold-500 text-xs uppercase tracking-widest">{{ $name }}</p>
                                    <span class="text-lg font-bold uppercase tracking-wide {{ $rating ? ($rating->score == 'liked' ? 'text-green-400' : ($rating->score == 'disliked' ? 'text-red-400' : 'text-gray-300')) : 'text-gold-800 italic' }}">
                                        {{ $rating ? $rating->score : 'Unrated' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @if($biggestDisagreement->movie->poster_path)
                        <img src="https://image.tmdb.org/t/p/w200{{ $biggestDisagreement->movie->poster_path }}" class="w-24 h-36 object-cover rounded shadow-lg shadow-black group-hover:scale-105 transition-transform z-10 relative">
                    @endif
                </div>
                @endif
            </div>

            <!-- Bottom Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12 text-center">
                <div class="bg-noir-950/80 border border-gold-900/50 p-6 rounded-lg">
                    <p class="text-gold-600 text-xs uppercase tracking-widest mb-2 font-bold">Favorite Cinema</p>
                    <p class="text-xl font-display text-gold-300 font-bold truncate">{{ $topCinema }}</p>
                    <p class="text-gold-700 text-sm mt-1">{{ $topCinemaVisits }} visits</p>
                </div>
                
                <div class="bg-noir-950/80 border border-gold-900/50 p-6 rounded-lg">
                    <p class="text-gold-600 text-xs uppercase tracking-widest mb-2 font-bold">Total Spent</p>
                    <p class="text-xl font-display text-gold-300 font-bold">{{ number_format($totalSpent, 0, ',', '.') }} kr.</p>
                </div>
                
                <div class="bg-noir-950/80 border border-gold-900/50 p-6 rounded-lg">
                    <p class="text-gold-600 text-xs uppercase tracking-widest mb-2 font-bold">Snack Battles</p>
                    <p class="text-gold-300 text-sm">
                        Alex: <span class="font-bold text-gold-100">{{ $alexSnacks }}</span> vs 
                        Casper: <span class="font-bold text-gold-100">{{ $casperSnacks }}</span>
                    </p>
                </div>
            </div>

            <div class="text-center mt-16">
                <a href="{{ route('dashboard') }}" class="inline-block px-8 py-3 bg-gold-600 hover:bg-gold-500 text-noir-900 font-bold uppercase tracking-widest rounded transition-colors shadow-[0_0_15px_var(--color-gold-700)]">
                    Back to Reality
                </a>
            </div>
        @endif
    </div>
</x-layouts.app>
