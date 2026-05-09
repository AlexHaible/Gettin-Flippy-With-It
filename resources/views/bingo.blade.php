<x-layouts.app>
<div class="container mx-auto px-4 py-8">
    <div class="relative z-10 mb-8 text-center">
        <div class="deco-divider w-56 mx-auto mb-4"></div>
        <h1 class="text-4xl font-display font-bold text-gold-500 text-shadow-gold uppercase tracking-[0.2em]">
            The Gauntlet
        </h1>
        <p class="text-gold-600 text-sm mt-2 tracking-widest">Cinema Bingo</p>
        <div class="deco-divider w-56 mx-auto mt-4"></div>
    </div>

    <div class="mb-6 flex justify-between items-center flex-wrap gap-4">
        <a href="{{ route('dashboard') }}" class="text-gold-500 hover:text-gold-300 font-semibold flex items-center transition-colors text-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Dashboard
        </a>
    </div>

    <livewire:bingo-board />
</div>
</x-layouts.app>
