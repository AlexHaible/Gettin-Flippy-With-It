<x-layouts.app>
<div class="container mx-auto px-4 py-8">
    <div class="relative z-10 mb-8 text-center">
        <div class="deco-divider w-56 mx-auto mb-4"></div>
        <h1 class="text-4xl font-display font-bold text-gold-500 text-shadow-gold uppercase tracking-[0.2em]">
            Stub Book
        </h1>
        <p class="text-gold-600 text-sm mt-2 tracking-widest">{{ $showings->count() }} tickets in the archive</p>
        <div class="deco-divider w-56 mx-auto mt-4"></div>
    </div>

    <div class="mb-6">
        <a href="{{ route('dashboard') }}" class="text-gold-500 hover:text-gold-300 font-semibold flex items-center transition-colors">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Dashboard
        </a>
    </div>

    @forelse($showings->groupBy(fn($s) => $s->start_time->year) as $year => $yearShowings)
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
                    <div class="group relative bg-noir-950 border border-gold-900/40 rounded overflow-hidden hover:border-gold-600 transition-all duration-300 hover:shadow-[0_0_15px_rgba(212,175,55,0.2)]">
                        {{-- Poster --}}
                        @if($showing->movie->poster_path)
                            <div class="relative overflow-hidden">
                                <img src="https://image.tmdb.org/t/p/w200{{ $showing->movie->poster_path }}"
                                     class="w-full object-cover transition duration-500 group-hover:scale-105 opacity-90 group-hover:opacity-100"
                                     alt="{{ $showing->movie->title }}">
                                {{-- Hover overlay --}}
                                <div class="absolute inset-0 bg-gradient-to-t from-noir-950 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition"></div>
                            </div>
                        @else
                            <div class="w-full h-40 bg-noir-900 flex items-center justify-center text-gold-800 text-xs text-center p-2 border-b border-gold-900/30">
                                {{ $showing->movie->title }}
                            </div>
                        @endif

                        {{-- Info strip --}}
                        <div class="p-2">
                            <p class="text-gold-200 text-xs font-bold font-display truncate leading-tight">{{ $showing->movie->title }}</p>
                            <p class="text-gold-700 text-[10px] mt-0.5">{{ $showing->start_time->format('d M Y') }}</p>
                            <p class="text-gold-800 text-[10px]">{{ $showing->cinema->name }}</p>

                            {{-- Rating pills --}}
                            @if($alexRating || $casperRating)
                                <div class="flex gap-1 mt-1.5">
                                    @if($alexRating)
                                        <span class="text-[10px]" title="Alex: {{ $alexRating->score }}">{{ $emojiMap[$alexRating->score] ?? '' }}</span>
                                    @endif
                                    @if($casperRating)
                                        <span class="text-[10px]" title="Casper: {{ $casperRating->score }}">{{ $emojiMap[$casperRating->score] ?? '' }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <p class="text-gold-700 italic text-center">No showings in the archive yet.</p>
    @endforelse
</div>
</x-layouts.app>
