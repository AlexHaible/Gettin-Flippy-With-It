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
            <div class="flex justify-between items-center border-b border-gold-900/30 pb-4 last:border-0 last:pb-0">
                <div>
                    <h4 class="text-lg font-semibold text-gold-100 font-display">{{ $showing->movie->title }}</h4>
                    <p class="text-gold-600 text-sm">{{ $showing->cinema->name }} • {{ $showing->hall_name ?? 'N/A' }}</p>
                    <p class="text-gold-700 text-xs">{{ $showing->start_time->format('d/m/Y H:i') }}</p>
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
</div>
