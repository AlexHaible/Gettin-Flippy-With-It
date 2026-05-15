<div class="container mx-auto px-4 py-8">
    <div class="relative z-10 mb-12 text-center">
        <div class="deco-divider w-56 mx-auto mb-4"></div>
        <h1 class="text-4xl font-display font-bold text-gold-500 text-shadow-gold uppercase tracking-[0.2em] mb-2">
            The Catalog</h1>
        <p class="text-gold-400 font-serif tracking-widest uppercase text-xs">Explore your cinematic universe</p>
        <div class="deco-divider w-56 mx-auto mt-4"></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        {{-- Genres Section --}}
        <div>
            <h2 class="text-2xl font-display font-bold text-gold-500 mb-6 tracking-widest uppercase flex items-center gap-3">
                <span class="w-12 h-[1px] bg-gold-900"></span>
                Genres
                <span class="flex-1 h-[1px] bg-gold-900"></span>
            </h2>
            <div class="flex flex-wrap gap-3">
                @foreach($genres as $name => $count)
                    <a href="{{ route('genre', ['name' => $name]) }}" 
                       class="group bg-noir-900/60 hover:bg-gold-900/20 border border-gold-900/40 hover:border-gold-500 rounded-lg px-4 py-3 transition-all duration-300 flex items-center justify-between min-w-[140px] flex-1 sm:flex-initial">
                        <span class="text-gold-100 group-hover:text-gold-400 font-medium tracking-wide transition-colors">{{ $name }}</span>
                        <span class="text-[10px] bg-noir-950 text-gold-600 px-1.5 py-0.5 rounded border border-gold-900/50 group-hover:bg-gold-900/50 group-hover:text-gold-200 transition-colors">{{ $count }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Actors Section --}}
        <div>
            <h2 class="text-2xl font-display font-bold text-gold-500 mb-6 tracking-widest uppercase flex items-center gap-3">
                <span class="w-12 h-[1px] bg-gold-900"></span>
                Cast
                <span class="flex-1 h-[1px] bg-gold-900"></span>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($actors as $name => $count)
                    <a href="{{ route('actor', ['name' => $name]) }}" 
                       class="group bg-noir-900/60 hover:bg-gold-900/20 border border-gold-900/40 hover:border-gold-500 rounded-lg px-4 py-2 transition-all duration-300 flex items-center justify-between">
                        <span class="text-gold-100 group-hover:text-gold-400 font-medium tracking-wide truncate transition-colors">{{ $name }}</span>
                        <span class="text-[10px] bg-noir-950 text-gold-600 px-1.5 py-0.5 rounded border border-gold-900/50 group-hover:bg-gold-900/50 group-hover:text-gold-200 transition-colors shrink-0">{{ $count }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
