<div class="container mx-auto px-4 py-8">
    <div class="relative z-10 mb-8 text-center">
        <div class="deco-divider w-56 mx-auto mb-4"></div>
        <h1 class="text-4xl font-display font-bold text-gold-500 text-shadow-gold uppercase tracking-[0.2em]">
            Watched Movies
        </h1>
        <div class="deco-divider w-56 mx-auto mt-4"></div>
    </div>

    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('dashboard') }}" class="text-gold-500 hover:text-gold-300 font-semibold flex items-center transition-colors">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Dashboard
        </a>
        <span class="text-gold-700 text-sm">Showing {{ $showings->count() }} of {{ $showings->total() }}</span>
    </div>

    <div class="bg-noir-900/90 deco-border-metallic border-2 rounded-lg p-6 shadow-lg shadow-gold-900/20">
        <div class="space-y-4">
            @forelse($showings as $showing)
            <div wire:click="openModal({{ $showing->id }})" class="flex justify-between items-center border-b border-gold-900/30 pb-4 last:border-0 last:pb-0 cursor-pointer hover:bg-noir-800 transition-colors px-2 -mx-2 rounded">
                <div class="flex items-center space-x-4">
                    @if($showing->movie->poster_path)
                        <img src="https://image.tmdb.org/t/p/w200{{ $showing->movie->poster_path }}" alt="{{ $showing->movie->title }} Poster" class="w-16 h-24 object-cover rounded shadow-sm shadow-gold-900/50">
                    @else
                        <div class="w-16 h-24 bg-noir-800 rounded flex items-center justify-center text-gold-700 text-xs border border-gold-900/50">No Poster</div>
                    @endif
                    <div>
                        <h4 class="text-lg font-semibold text-gold-100 font-display">{{ $showing->movie->title }}</h4>
                        <p class="text-gold-600 text-sm">{{ $showing->cinema->name }} • {{ $showing->hall_name ?? 'N/A' }}</p>
                        <p class="text-gold-700 text-xs">{{ $showing->start_time->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="block text-gold-400 font-bold">{{ number_format($showing->price_total, 0, ',', '.') }} kr.</span>
                </div>
            </div>
            @empty
            <p class="text-gold-700 italic">No showings found.</p>
            @endforelse
        </div>
        
        @if($showings->hasMorePages())
            <!-- Infinite Scroll Trigger -->
            <div x-intersect="$wire.loadMore()" class="mt-6 flex justify-center pb-4">
                <div class="w-8 h-8 border-4 border-gold-900 border-t-gold-500 rounded-full animate-spin"></div>
            </div>
        @else
            <div class="mt-6 text-center text-gold-800 text-sm italic border-t border-gold-900/30 pt-4">
                End of list
            </div>
        @endif
    </div>
    @if($showModal && $selectedShowing)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         x-data="{ 
            downloadTicket() {
                const ticketEl = document.getElementById('ticket-card');
                html2canvas(ticketEl, { backgroundColor: '#0a0a0a', scale: 2 }).then(canvas => {
                    const link = document.createElement('a');
                    link.download = 'popcorn-ticket-{{ \Illuminate\Support\Str::slug($selectedShowing->movie->title) }}.png';
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                });
            }
         }">
        <div class="bg-noir-900 border-2 deco-border-metallic rounded-lg shadow-2xl max-w-2xl w-full p-6 relative" @click.away="$wire.closeModal()">
            <button wire:click="closeModal" class="absolute top-4 right-4 text-gold-500 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            
            <div id="ticket-card" class="flex flex-col md:flex-row gap-6 p-6 rounded bg-noir-950 border border-gold-900/50 relative overflow-hidden">
                @if($selectedShowing->movie->poster_path)
                    <img src="https://image.tmdb.org/t/p/w300{{ $selectedShowing->movie->poster_path }}" class="w-32 md:w-48 rounded shadow-lg shadow-black z-10 relative">
                @endif
                <div class="z-10 relative flex-1 flex flex-col justify-between">
                    <div>
                        <p class="text-gold-600 text-xs uppercase tracking-widest mb-1 font-bold">Admit Two</p>
                        <h2 class="text-3xl font-display text-gold-100 font-bold leading-tight">{{ $selectedShowing->movie->title }}</h2>
                        <p class="text-gold-500 mt-2 font-semibold">{{ $selectedShowing->cinema->name }} • {{ $selectedShowing->hall_name ?? 'N/A' }}</p>
                        <p class="text-gold-700 text-sm mt-1">{{ $selectedShowing->start_time->format('l, jS M Y H:i') }}</p>
                        
                        <div class="mt-3 flex flex-wrap gap-2">
                            @if($selectedShowing->movie->genres)
                                @foreach(json_decode($selectedShowing->movie->genres, true) ?? [] as $genre)
                                    <span class="text-xs bg-gold-900/30 text-gold-400 px-2 py-0.5 rounded border border-gold-800">{{ $genre }}</span>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <div class="mt-6 border-t border-gold-900/50 pt-4">
                        <p class="text-gold-600 text-xs uppercase tracking-widest mb-2 font-bold">Ratings</p>
                        <div class="flex flex-col gap-2">
                            @foreach(['Alex' => 1, 'Casper' => 2] as $name => $uid)
                                @php
                                    $rating = $selectedShowing->ratings->firstWhere('user_id', $uid);
                                @endphp
                                <div class="flex items-center justify-between">
                                    <span class="text-gold-300 font-bold">{{ $name }}</span>
                                    <span class="text-sm px-2 py-1 rounded font-bold uppercase tracking-wide {{ $rating ? ($rating->score == 'liked' ? 'bg-green-900/50 text-green-400 border border-green-800' : ($rating->score == 'disliked' ? 'bg-red-900/50 text-red-400 border border-red-800' : 'bg-gray-800 text-gray-300 border border-gray-600')) : 'text-gold-800 italic border border-transparent' }}">
                                        {{ $rating ? $rating->score : 'Unrated' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @if($selectedShowing->movie->backdrop_path)
                    <div class="absolute inset-0 opacity-10 mix-blend-screen" style="background-image: url('https://image.tmdb.org/t/p/w500{{ $selectedShowing->movie->backdrop_path }}'); background-size: cover; background-position: center;"></div>
                @endif
            </div>

            <div class="mt-6 flex flex-col md:flex-row items-center justify-between gap-4 border-t border-gold-900/50 pt-6">
                <div class="flex gap-2">
                    <button wire:click="rateShowing('liked')" class="px-4 py-2 bg-green-900/30 hover:bg-green-900/60 text-green-400 border border-green-800 rounded transition font-bold">👍 Liked</button>
                    <button wire:click="rateShowing('meh')" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 border border-gray-600 rounded transition font-bold">😐 Meh</button>
                    <button wire:click="rateShowing('disliked')" class="px-4 py-2 bg-red-900/30 hover:bg-red-900/60 text-red-400 border border-red-800 rounded transition font-bold">👎 Disliked</button>
                </div>
                
                <button @click="downloadTicket()" class="px-4 py-2 bg-gold-600 hover:bg-gold-500 text-noir-900 font-bold rounded flex items-center gap-2 transition shadow-[0_0_10px_var(--color-gold-700)]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Share
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
