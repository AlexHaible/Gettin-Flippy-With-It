<div class="container mx-auto px-4 py-8">
    <div class="relative z-10 mb-8 text-center">
        <div class="deco-divider w-56 mx-auto mb-4"></div>
        <h1 class="text-4xl font-display font-bold text-gold-500 text-shadow-gold uppercase tracking-[0.2em]">
            Watched Movies
        </h1>
        <div class="deco-divider w-56 mx-auto mt-4"></div>
    </div>

    {{-- Toolbar --}}
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-center gap-4">
        <a href="{{ route('dashboard') }}" class="text-gold-500 hover:text-gold-300 font-semibold flex items-center transition-colors text-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Dashboard
        </a>

        {{-- View Toggle --}}
        <div class="flex items-center gap-1 bg-noir-900 border border-gold-900/50 rounded-lg p-1">
            <button wire:click="setView('list')"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded text-[10px] sm:text-sm font-semibold transition
                    {{ $viewMode === 'list' ? 'bg-gold-600 text-noir-900' : 'text-gold-500 hover:text-gold-300' }}">
                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                List
            </button>
            <button wire:click="setView('grid')"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded text-[10px] sm:text-sm font-semibold transition
                    {{ $viewMode === 'grid' ? 'bg-gold-600 text-noir-900' : 'text-gold-500 hover:text-gold-300' }}">
                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                Stubs
            </button>
        </div>

        <span class="text-gold-700 text-[10px] sm:text-sm uppercase tracking-widest">
            @if($viewMode === 'list')
                {{ $showings->count() }} / {{ $showings->total() }}
            @else
                {{ $showings->count() }} Films
            @endif
        </span>
    </div>

    {{-- ══════════════════════════════════════════════
         LIST VIEW
    ══════════════════════════════════════════════ --}}
    @if($viewMode === 'list')
    <div class="bg-noir-900/90 deco-border-metallic border-2 rounded-lg p-6 shadow-lg shadow-gold-900/20">
        <div class="space-y-4">
            @forelse($showings as $showing)
            <div wire:click="openModal({{ $showing->id }})"
                 class="flex justify-between items-center border-b border-gold-900/30 pb-4 last:border-0 last:pb-0 cursor-pointer hover:bg-noir-800 transition-colors px-2 -mx-2 rounded">
                <div class="flex items-center space-x-4">
                    @if($showing->movie->poster_path)
                        <img src="https://image.tmdb.org/t/p/w200{{ $showing->movie->poster_path }}"
                             alt="{{ $showing->movie->title }} Poster"
                             class="w-16 h-24 object-cover rounded shadow-sm shadow-gold-900/50">
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
                    @php $myRating = $showing->ratings->firstWhere('user_id', auth()->id()); @endphp
                    @if($myRating)
                        <span class="text-xs text-gold-700 mt-1 block">
                            {{ ['liked' => '👍', 'meh' => '😐', 'disliked' => '👎'][$myRating->score] ?? '' }}
                        </span>
                    @endif
                </div>
            </div>
            @empty
            <p class="text-gold-700 italic">No showings found.</p>
            @endforelse
        </div>

        @if($showings->hasMorePages())
            <div x-intersect="$wire.loadMore()" class="mt-6 flex justify-center pb-4">
                <div class="w-8 h-8 border-4 border-gold-900 border-t-gold-500 rounded-full animate-spin"></div>
            </div>
        @else
            <div class="mt-6 text-center text-gold-800 text-sm italic border-t border-gold-900/30 pt-4">End of list</div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════
         GRID / STUB BOOK VIEW
    ══════════════════════════════════════════════ --}}
    @else
    @foreach($showings->groupBy(fn($s) => $s->start_time->year) as $year => $yearShowings)
        <div class="mb-10">
            <div class="flex items-center gap-4 mb-4">
                <h2 class="text-2xl font-display font-bold text-gold-500">{{ $year }}</h2>
                <div class="flex-1 h-px bg-gold-900/40"></div>
                <span class="text-gold-700 text-sm">{{ $yearShowings->count() }} films</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                @foreach($yearShowings as $showing)
                    @php
                        $alexRating   = $showing->ratings->firstWhere('user_id', 1);
                        $casperRating = $showing->ratings->firstWhere('user_id', 2);
                        $emojiMap     = ['liked' => '👍', 'meh' => '😐', 'disliked' => '👎'];
                    @endphp
                    <div wire:click="openModal({{ $showing->id }})"
                         class="group relative bg-noir-950 border border-gold-900/40 rounded overflow-hidden cursor-pointer hover:border-gold-600 transition-all duration-300 hover:shadow-[0_0_15px_rgba(212,175,55,0.2)]">
                        @if($showing->movie->poster_path)
                            <div class="relative overflow-hidden">
                                <img src="https://image.tmdb.org/t/p/w200{{ $showing->movie->poster_path }}"
                                     class="w-full object-cover transition duration-500 group-hover:scale-105 opacity-90 group-hover:opacity-100"
                                     alt="{{ $showing->movie->title }}">
                                <div class="absolute inset-0 bg-gradient-to-t from-noir-950 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition"></div>
                            </div>
                        @else
                            <div class="w-full h-40 bg-noir-900 flex items-center justify-center text-gold-800 text-xs text-center p-2 border-b border-gold-900/30">
                                {{ $showing->movie->title }}
                            </div>
                        @endif
                        <div class="p-2">
                            <p class="text-gold-200 text-xs font-bold font-display truncate leading-tight">{{ $showing->movie->title }}</p>
                            <p class="text-gold-700 text-[10px] mt-0.5">{{ $showing->start_time->format('d M Y') }}</p>
                            <p class="text-gold-800 text-[10px]">{{ $showing->cinema->name }}</p>
                            @if($alexRating || $casperRating)
                                <div class="flex gap-1 mt-1.5">
                                    @if($alexRating)<span class="text-[10px]" title="Alex: {{ $alexRating->score }}">{{ $emojiMap[$alexRating->score] ?? '' }}</span>@endif
                                    @if($casperRating)<span class="text-[10px]" title="Casper: {{ $casperRating->score }}">{{ $emojiMap[$casperRating->score] ?? '' }}</span>@endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
    @endif

    {{-- ══════════════════════════════════════════════
         RATING / TICKET MODAL (shared by both views)
    ══════════════════════════════════════════════ --}}
    @if($showModal && $selectedShowing)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-2 sm:p-4 bg-black/90 backdrop-blur-sm"
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
        <div class="bg-noir-900 border-2 deco-border-metallic rounded-lg shadow-2xl max-w-2xl w-full p-4 sm:p-6 relative max-h-[95vh] overflow-y-auto" @click.away="$wire.closeModal()">
            <button wire:click="closeModal" class="sticky top-0 left-full block ml-auto z-[70] text-gold-500 hover:text-white -mt-2 -mr-2 mb-2 p-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <div id="ticket-card" class="flex flex-col md:flex-row gap-4 sm:gap-6 p-4 sm:p-6 rounded bg-noir-950 border border-gold-900/50 relative overflow-hidden">
                @if($selectedShowing->movie->poster_path)
                    <div class="flex justify-center md:block">
                        <img src="https://image.tmdb.org/t/p/w300{{ $selectedShowing->movie->poster_path }}" class="w-28 sm:w-32 md:w-48 rounded shadow-lg shadow-black z-10 relative">
                    </div>
                @endif
                <div class="z-10 relative flex-1 flex flex-col justify-between">
                    <div>
                        <p class="text-gold-600 text-[10px] sm:text-xs uppercase tracking-widest mb-1 font-bold text-center md:text-left">Admit Two</p>
                        <h2 class="text-2xl sm:text-3xl font-display text-gold-100 font-bold leading-tight text-center md:text-left">{{ $selectedShowing->movie->title }}</h2>
                        <p class="text-gold-500 mt-2 font-semibold text-center md:text-left text-sm sm:text-base">{{ $selectedShowing->cinema->name }} • {{ $selectedShowing->hall_name ?? 'N/A' }}</p>
                        <p class="text-gold-700 text-xs sm:text-sm mt-1 text-center md:text-left">{{ $selectedShowing->start_time->format('l, jS M Y H:i') }}</p>

                        <div class="mt-3 flex flex-wrap justify-center md:justify-start gap-1.5 sm:gap-2">
                            @if($selectedShowing->movie->genres)
                                @foreach(json_decode($selectedShowing->movie->genres, true) ?? [] as $genre)
                                    <span class="text-[10px] sm:text-xs bg-gold-900/30 text-gold-400 px-2 py-0.5 rounded border border-gold-800">{{ $genre }}</span>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <div class="mt-6 border-t border-gold-900/50 pt-4">
                        <p class="text-gold-600 text-[10px] sm:text-xs uppercase tracking-widest mb-2 font-bold">Ratings</p>
                        <div class="flex flex-col gap-2">
                            @foreach(['Alex' => 1, 'Casper' => 2] as $name => $uid)
                                @php $rating = $selectedShowing->ratings->firstWhere('user_id', $uid); @endphp
                                <div class="flex items-center justify-between">
                                    <span class="text-gold-300 font-bold text-sm sm:text-base">{{ $name }}</span>
                                    <span class="text-[10px] sm:text-xs px-2 py-1 rounded font-bold uppercase tracking-wide {{ $rating ? ($rating->score == 'liked' ? 'bg-green-900/50 text-green-400 border border-green-800' : ($rating->score == 'disliked' ? 'bg-red-900/50 text-red-400 border border-red-800' : 'bg-gray-800 text-gray-300 border border-gray-600')) : 'text-gold-800 italic border border-transparent' }}">
                                        {{ $rating ? $rating->score : 'Unrated' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @if($selectedShowing->movie->backdrop_path)
                    <div class="absolute inset-0 opacity-10 mix-blend-screen pointer-events-none" style="background-image: url('https://image.tmdb.org/t/p/w500{{ $selectedShowing->movie->backdrop_path }}'); background-size: cover; background-position: center;"></div>
                @endif
            </div>

            <div class="mt-6 flex flex-col items-stretch gap-4 border-t border-gold-900/50 pt-6">
                <div class="grid grid-cols-3 gap-2">
                    <button wire:click="rateShowing('liked')" class="flex flex-col sm:flex-row items-center justify-center gap-1 px-2 py-2 sm:py-3 bg-green-900/20 hover:bg-green-900/40 text-green-400 border border-green-800 rounded transition font-bold text-xs sm:text-sm">
                        <span>👍</span> <span class="hidden sm:inline">Liked</span>
                    </button>
                    <button wire:click="rateShowing('meh')" class="flex flex-col sm:flex-row items-center justify-center gap-1 px-2 py-2 sm:py-3 bg-noir-800 hover:bg-noir-700 text-gold-500 border border-gold-900/50 rounded transition font-bold text-xs sm:text-sm">
                        <span>😐</span> <span class="hidden sm:inline">Meh</span>
                    </button>
                    <button wire:click="rateShowing('disliked')" class="flex flex-col sm:flex-row items-center justify-center gap-1 px-2 py-2 sm:py-3 bg-red-900/20 hover:bg-red-900/40 text-red-400 border border-red-800 rounded transition font-bold text-xs sm:text-sm">
                        <span>👎</span> <span class="hidden sm:inline">Disliked</span>
                    </button>
                </div>

                <button @click="downloadTicket()" class="w-full py-3 bg-gold-600 hover:bg-gold-500 text-noir-900 font-bold rounded flex items-center justify-center gap-2 transition shadow-[0_0_15px_rgba(212,175,55,0.3)]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>Share Ticket Image</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
