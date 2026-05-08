<x-layouts.app>
    <div class="container mx-auto px-4 py-8">
        <div class="relative z-10 mb-8 text-center">
            <div class="deco-divider w-56 mx-auto mb-4"></div>
            <p class="text-gold-600 text-sm uppercase tracking-widest mb-2 font-bold">{{ $entityType }} Showcase</p>
            <h1 class="text-4xl font-display font-bold text-gold-500 text-shadow-gold uppercase tracking-[0.2em]">
                {{ $entityName }}
            </h1>
            <div class="deco-divider w-56 mx-auto mt-4"></div>
        </div>

        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route('dashboard') }}" class="text-gold-500 hover:text-gold-300 font-semibold flex items-center transition-colors">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Dashboard
            </a>
            <span class="text-gold-700 text-sm">{{ $showings->count() }} Movies</span>
        </div>

        <div class="bg-noir-900/90 deco-border-metallic border-2 rounded-lg p-6 shadow-lg shadow-gold-900/20">
            <div class="space-y-4">
                @forelse($showings as $showing)
                <div class="flex justify-between items-center border-b border-gold-900/30 pb-4 last:border-0 last:pb-0">
                    <div class="flex items-center space-x-4">
                        @if($showing->movie->poster_path)
                            <img src="https://image.tmdb.org/t/p/w200{{ $showing->movie->poster_path }}" class="w-16 h-24 object-cover rounded shadow-sm shadow-gold-900/50">
                        @else
                            <div class="w-16 h-24 bg-noir-800 rounded flex items-center justify-center text-gold-700 text-xs border border-gold-900/50">No Poster</div>
                        @endif
                        <div>
                            <h4 class="text-lg font-semibold text-gold-100 font-display">{{ $showing->movie->title }}</h4>
                            <p class="text-gold-600 text-sm">{{ $showing->cinema->name }}</p>
                            <p class="text-gold-700 text-xs">{{ $showing->start_time->format('d/m/Y') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        @if($showing->ratings->count() > 0)
                            <span class="text-xs bg-gold-900/30 text-gold-400 px-2 py-1 rounded border border-gold-800">Rated</span>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-gold-700 italic">No movies found for this {{ strtolower($entityType) }}.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.app>
