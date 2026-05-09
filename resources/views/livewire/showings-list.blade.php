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
    <div class="fixed inset-0 z-[500] flex items-center justify-center p-2 sm:p-4 bg-black/90"
         x-data="{
            downloadTicket() {
                const ticketEl = document.getElementById('ticket-card');
                html2canvas(ticketEl, {
                    backgroundColor: '#050505', scale: 2, useCORS: true, logging: false,
                    onclone: (clonedDoc) => {
                        const el = clonedDoc.getElementById('ticket-card');
                        const clean = (node) => {
                            const s = window.getComputedStyle(node);
                            if (s.color.includes('okl')) node.style.color = '#F9F1D8';
                            if (s.backgroundColor.includes('okl')) node.style.backgroundColor = '#050505';
                            if (s.borderColor.includes('okl')) node.style.borderColor = '#2B230B';
                            node.style.borderImage = 'none'; node.style.textShadow = 'none';
                            node.style.boxShadow = 'none'; node.style.backdropFilter = 'none';
                            if (s.backgroundImage.includes('gradient')) node.style.backgroundImage = 'none';
                            Array.from(node.children).forEach(clean);
                        };
                        clean(el);
                        el.style.background = '#050505'; el.style.border = '1px solid #2B230B';
                    }
                }).then(canvas => {
                    const link = document.createElement('a');
                    link.download = 'ticket-{{ \Illuminate\Support\Str::slug($selectedShowing->movie->title) }}.png';
                    link.href = canvas.toDataURL('image/png'); link.click();
                }).catch(err => console.error('Capture failed:', err));
            }
         }"
         x-init="document.body.style.overflow='hidden'; return () => document.body.style.overflow='';">
        <div class="bg-noir-900 border deco-border-metallic rounded-lg shadow-2xl max-w-2xl w-full p-4 sm:p-6 relative max-h-[90vh] overflow-y-auto"
             @click.outside="$wire.closeModal()">
            <button wire:click="closeModal" class="absolute top-3 right-3 text-gold-500 hover:text-white p-1 z-10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <div id="ticket-card" style="background-color:#050505;border:1px solid #2B230B;" class="flex flex-col md:flex-row gap-4 p-4 sm:p-6 rounded relative overflow-hidden">
                @if($selectedShowing->movie->poster_path)
                    <div class="flex justify-center md:block shrink-0">
                        <img src="https://image.tmdb.org/t/p/w300{{ $selectedShowing->movie->poster_path }}" crossorigin="anonymous" class="w-28 sm:w-32 md:w-40 rounded shadow-lg z-10 relative">
                    </div>
                @endif
                <div class="z-10 relative flex-1 flex flex-col justify-between min-w-0">
                    <div>
                        <p style="color:#806921;" class="text-[10px] uppercase tracking-widest mb-1 font-bold text-center md:text-left">Admit Two</p>
                        <h2 style="color:#F9F1D8;" class="text-2xl sm:text-3xl font-display font-bold leading-tight text-center md:text-left">{{ $selectedShowing->movie->title }}</h2>
                        <p style="color:#D4AF37;" class="mt-1 font-semibold text-center md:text-left text-sm">{{ $selectedShowing->cinema->name }} • {{ $selectedShowing->hall_name ?? 'N/A' }}</p>
                        <p style="color:#806921;" class="text-xs mt-1 text-center md:text-left">{{ $selectedShowing->start_time->format('l, jS M Y H:i') }}</p>
                        <div class="mt-3 flex flex-wrap justify-center md:justify-start gap-1.5">
                            @foreach(json_decode($selectedShowing->movie->genres ?? '[]', true) as $genre)
                                <span style="background:#1a1a1a;color:#DDB956;border:1px solid #554616;display:inline-flex;align-items:center;line-height:1;" class="text-[10px] px-2 py-1 rounded font-bold uppercase tracking-wide">{{ $genre }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div style="border-top:1px solid #2B230B;" class="mt-4 pt-3">
                        <p style="color:#806921;" class="text-[10px] uppercase tracking-widest mb-2 font-bold">Ratings</p>
                        @foreach(['Alex' => 1, 'Casper' => 2] as $name => $uid)
                            @php $rating = $selectedShowing->ratings->firstWhere('user_id', $uid); @endphp
                            <div class="flex items-center justify-between mb-1">
                                <span style="color:#E6CE7D;" class="font-bold text-sm">{{ $name }}</span>
                                @if($rating)
                                    <span style="background:{{ $rating->score=='liked'?'rgba(6,78,59,.5)':($rating->score=='disliked'?'rgba(127,29,29,.5)':'rgba(31,41,55,.5)') }};color:{{ $rating->score=='liked'?'#34d399':($rating->score=='disliked'?'#f87171':'#d1d5db') }};border:1px solid {{ $rating->score=='liked'?'#064e3b':($rating->score=='disliked'?'#7f1d1d':'#374151') }};" class="text-[10px] px-2 py-0.5 rounded font-bold uppercase">{{ $rating->score }}</span>
                                @else
                                    <span style="color:#554616;" class="text-[10px] italic">Unrated</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                @if($selectedShowing->movie->backdrop_path)
                    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image:url('https://image.tmdb.org/t/p/w500{{ $selectedShowing->movie->backdrop_path }}');background-size:cover;mix-blend-mode:screen;"></div>
                @endif
            </div>

            <div class="mt-4 space-y-3 border-t border-gold-900/50 pt-4">
                <div class="grid grid-cols-3 gap-2">
                    <button wire:click="rateShowing('liked')" class="flex flex-col items-center justify-center gap-1 py-3 bg-green-900/20 hover:bg-green-900/40 text-green-400 border border-green-800 rounded transition font-bold text-xs">👍<span>Liked</span></button>
                    <button wire:click="rateShowing('meh')" class="flex flex-col items-center justify-center gap-1 py-3 bg-noir-800 hover:bg-noir-700 text-gold-500 border border-gold-900/50 rounded transition font-bold text-xs">😐<span>Meh</span></button>
                    <button wire:click="rateShowing('disliked')" class="flex flex-col items-center justify-center gap-1 py-3 bg-red-900/20 hover:bg-red-900/40 text-red-400 border border-red-800 rounded transition font-bold text-xs">👎<span>Disliked</span></button>
                </div>
                <button @click="downloadTicket()" class="w-full py-3 bg-gold-600 hover:bg-gold-500 text-noir-900 font-bold rounded flex items-center justify-center gap-2 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Share Ticket Image
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
