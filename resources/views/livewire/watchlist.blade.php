<div class="container mx-auto px-4 py-8">
    <div class="relative z-10 mb-8 text-center">
        <div class="deco-divider w-56 mx-auto mb-4"></div>
        <h1 class="text-4xl font-display font-bold text-gold-500 text-shadow-gold uppercase tracking-[0.2em]">
            Anticipated Watchlist
        </h1>
        <div class="deco-divider w-56 mx-auto mt-4"></div>
    </div>

    <!-- Search Bar -->
    <div class="max-w-2xl mx-auto mb-12 relative">
        <div class="relative">
            <input type="text" wire:model.live.debounce.500ms="searchQuery" placeholder="Search for upcoming movies..." class="w-full bg-noir-900 border-2 border-gold-900/50 rounded-lg px-4 py-3 text-gold-100 focus:outline-none focus:border-gold-500 transition shadow-[0_0_15px_rgba(212,175,55,0.1)]">
            <div wire:loading wire:target="searchQuery" class="absolute right-4 top-3.5">
                <div class="w-5 h-5 border-2 border-gold-900 border-t-gold-500 rounded-full animate-spin"></div>
            </div>
        </div>

        @if(!empty($searchResults))
            <div class="absolute w-full mt-2 bg-noir-900 border border-gold-900 rounded-lg shadow-2xl z-50 overflow-hidden">
                @foreach($searchResults as $result)
                    <div wire:click="addMovie({{ $result['id'] }}, '{{ addslashes($result['title']) }}', '{{ $result['poster_path'] ?? '' }}', '{{ $result['release_date'] ?? '' }}')" class="flex items-center gap-4 p-3 hover:bg-noir-800 cursor-pointer border-b border-gold-900/30 last:border-0 transition">
                        @if(!empty($result['poster_path']))
                            <img src="https://image.tmdb.org/t/p/w92{{ $result['poster_path'] }}" class="w-10 h-14 rounded object-cover shadow-sm">
                        @else
                            <div class="w-10 h-14 bg-noir-950 rounded border border-gold-900/50 flex items-center justify-center text-xs text-gold-800">No Img</div>
                        @endif
                        <div>
                            <p class="text-gold-100 font-bold font-display">{{ $result['title'] }}</p>
                            <p class="text-gold-700 text-xs">{{ isset($result['release_date']) ? substr($result['release_date'], 0, 4) : 'Unknown' }}</p>
                        </div>
                        <div class="ml-auto text-gold-500 text-sm font-bold">
                            + Add
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Watchlist Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($watchlistMovies as $movie)
            @php
                $isMutual = $movie->users->count() >= 2;
                $hasAdded = $movie->users->contains('id', auth()->id());
            @endphp
            <div class="bg-noir-900/90 border-2 {{ $isMutual ? 'border-gold-500 shadow-[0_0_15px_rgba(212,175,55,0.3)]' : 'border-gold-900/30' }} rounded-lg overflow-hidden group relative transition-all hover:border-gold-700">
                @if($isMutual)
                    <div class="absolute top-2 right-2 z-20 bg-gold-500 text-noir-950 text-xs font-bold px-2 py-1 rounded shadow-lg flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"></path></svg>
                        MUTUAL HYPE
                    </div>
                @endif
                
                <div class="relative h-64 overflow-hidden">
                    @if($movie->poster_path)
                        <img src="https://image.tmdb.org/t/p/w500{{ $movie->poster_path }}" class="w-full h-full object-cover transition duration-500 group-hover:scale-110 opacity-80 group-hover:opacity-100">
                    @else
                        <div class="w-full h-full bg-noir-950 flex items-center justify-center text-gold-800">No Image</div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-noir-950 to-transparent"></div>
                    <div class="absolute bottom-4 left-4 right-4">
                        <h3 class="text-xl font-display font-bold text-gold-100 leading-tight drop-shadow-md">{{ $movie->title }}</h3>
                        <p class="text-gold-500 text-sm mt-1">{{ $movie->release_date ? $movie->release_date->format('M j, Y') : 'TBA' }}</p>
                    </div>
                </div>
                
                <div class="p-4 flex justify-between items-center bg-noir-950">
                    <div class="flex -space-x-2">
                        @foreach($movie->users as $user)
                            <div class="w-8 h-8 rounded-full bg-gold-600 border-2 border-noir-950 flex items-center justify-center text-noir-900 font-bold text-xs shadow-sm z-10" title="{{ $user->username }}">
                                {{ substr($user->username, 0, 1) }}
                            </div>
                        @endforeach
                    </div>
                    
                    <button wire:click="toggleHype({{ $movie->id }})" class="text-sm font-bold px-3 py-1.5 rounded transition border {{ $hasAdded ? 'bg-gold-900/30 text-gold-500 border-gold-700 hover:bg-gold-900/50' : 'bg-transparent text-gold-600 border-gold-900 hover:border-gold-500 hover:text-gold-400' }}">
                        {{ $hasAdded ? 'Added' : '+ Add' }}
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>
