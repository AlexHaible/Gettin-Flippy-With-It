<x-layouts.app>
<div class="container mx-auto px-4 py-8">
    <div class="relative z-10 mb-8 text-center">
        <div class="deco-divider w-56 mx-auto mb-4"></div>
        <h1 class="text-4xl font-display font-bold text-gold-500 text-shadow-gold uppercase tracking-[0.2em]">
            The Gauntlet {{ $year }}
        </h1>
        <p class="text-gold-600 text-sm mt-2 tracking-widest">Cinema Bingo</p>
        <div class="deco-divider w-56 mx-auto mt-4"></div>
    </div>

    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('dashboard') }}" class="text-gold-500 hover:text-gold-300 font-semibold flex items-center transition-colors">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Dashboard
        </a>
        @if($hasBoard)
            @php $completed = $goals->where('is_completed', true)->count(); @endphp
            <span class="text-gold-400 font-bold text-sm">{{ $completed }} / 25 complete</span>
        @endif
    </div>

    @if(!$hasBoard)
        <div class="bg-noir-900/90 deco-border-metallic border-2 rounded-lg p-12 text-center shadow-lg">
            <p class="text-gold-600 text-lg mb-4">No bingo board has been generated for {{ $year }} yet.</p>
            <code class="bg-noir-950 text-gold-400 px-4 py-2 rounded border border-gold-900/50 text-sm block max-w-xs mx-auto">php artisan bingo:generate</code>
        </div>
    @else
        {{-- Progress bar --}}
        @php $pct = round(($completed / 25) * 100); @endphp
        <div class="mb-6 bg-noir-900/90 deco-border-metallic border-2 rounded-lg p-4">
            <div class="flex justify-between text-xs text-gold-600 mb-2">
                <span>Progress</span><span>{{ $pct }}%</span>
            </div>
            <div class="w-full bg-noir-800 rounded-full h-3 border border-gold-900">
                <div class="bg-gradient-to-r from-gold-700 to-gold-400 h-3 rounded-full shadow-[0_0_8px_var(--color-gold-600)] transition-all duration-700"
                     style="width: {{ $pct }}%"></div>
            </div>
        </div>

        {{-- 5×5 Grid --}}
        <div class="grid grid-cols-5 gap-2">
            @foreach($goals as $goal)
                @php
                    $done       = $goal->is_completed;
                    $freeSquare = $goal->type === 'free_square';
                    $poster     = $done && $goal->showing?->movie?->poster_path
                                    ? 'https://image.tmdb.org/t/p/w200' . $goal->showing->movie->poster_path
                                    : null;
                @endphp

                @if(!$freeSquare)
                <form method="POST" action="{{ route('bingo.toggle', $goal) }}" class="contents">
                    @csrf
                @endif

                <button
                    type="{{ $freeSquare ? 'button' : 'submit' }}"
                    title="{{ $freeSquare ? 'Free Square!' : ($done ? 'Click to unmark' : 'Click to mark as completed') }}"
                    class="relative aspect-square rounded-lg border-2 overflow-hidden flex items-center justify-center text-center p-2 transition-all duration-300 w-full
                        {{ $done ? 'border-gold-500 shadow-[0_0_12px_rgba(212,175,55,0.5)]' : 'border-gold-900/40 bg-noir-900/90 hover:border-gold-700 hover:bg-noir-800' }}
                        {{ $freeSquare ? 'border-gold-600 bg-gold-950/30 cursor-default' : 'cursor-pointer' }}">

                    {{-- Poster backdrop when completed --}}
                    @if($poster)
                        <div class="absolute inset-0 bg-cover bg-center opacity-30"
                             style="background-image: url('{{ $poster }}')"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-noir-950/90 to-noir-950/30"></div>
                    @endif

                    {{-- Gold stamp overlay for completed --}}
                    @if($done)
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <svg class="w-16 h-16 text-gold-500 opacity-25" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    @endif

                    <div class="relative z-10">
                        <p class="text-xs font-bold leading-tight
                            {{ $done ? 'text-gold-300' : ($freeSquare ? 'text-gold-400' : 'text-gold-600') }}">
                            {{ $goal->title }}
                        </p>
                        @if($done && $goal->showing)
                            <p class="text-gold-700 text-[10px] mt-1 truncate max-w-full">
                                {{ $goal->showing->movie->title }}
                            </p>
                        @endif
                    </div>
                </button>

                @if(!$freeSquare)
                </form>
                @endif
            @endforeach
        </div>
    @endif
</div>
</x-layouts.app>
