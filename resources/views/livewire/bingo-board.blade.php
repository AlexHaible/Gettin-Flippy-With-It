<div>
    @php $hasBoard = $goals->isNotEmpty(); @endphp

    @if(!$hasBoard)
        <div class="bg-noir-900/90 deco-border-metallic border-2 rounded-lg p-12 text-center shadow-lg">
            <p class="text-gold-600 text-lg mb-4">No bingo board has been generated for {{ $year }} yet.</p>
            <code class="bg-noir-950 text-gold-400 px-4 py-2 rounded border border-gold-900/50 text-sm block max-w-xs mx-auto">php artisan bingo:generate</code>
        </div>
    @else
        {{-- Progress bar --}}
        <div class="mb-6 bg-noir-900/90 deco-border-metallic border-2 rounded-lg p-3">
            <div class="flex justify-between text-[10px] text-gold-600 mb-1 uppercase tracking-widest">
                <span>The Gauntlet Progress</span>
                <span>{{ $completedCount }} / 25 ({{ $progressPct }}%)</span>
            </div>
            <div class="w-full bg-noir-800 rounded-full h-2 border border-gold-900">
                <div class="bg-gradient-to-r from-gold-700 to-gold-400 h-2 rounded-full shadow-[0_0_8px_var(--color-gold-600)] transition-all duration-700"
                     style="width: {{ $progressPct }}%"></div>
            </div>
        </div>

        {{-- 5×5 Grid (More compact) --}}
        <div class="grid grid-cols-5 gap-1.5 max-w-xl mx-auto">
            @foreach($goals as $goal)
                @php
                    $done = $goal->is_completed;
                    $freeSquare = $goal->type === 'free_square';
                    $poster = $done && $goal->showing?->movie?->poster_path
                        ? 'https://image.tmdb.org/t/p/w200' . $goal->showing->movie->poster_path
                        : null;
                @endphp

                <button
                    wire:click="toggle({{ $goal->id }})"
                    @if($freeSquare) disabled @endif
                    title="{{ $freeSquare ? 'Free Square!' : ($done ? 'Click to unmark' : 'Click to mark as completed') }}"
                    class="relative aspect-square rounded-md border overflow-hidden flex items-center justify-center text-center p-1.5 transition-all duration-300 w-full
                        {{ $done ? 'border-gold-500 shadow-[0_0_8px_rgba(212,175,55,0.4)]' : 'border-gold-900/30 bg-noir-900/90 hover:border-gold-700 hover:bg-noir-800' }}
                        {{ $freeSquare ? 'border-gold-600 bg-gold-950/30 cursor-default' : 'cursor-pointer' }}">

                    {{-- Poster backdrop when completed --}}
                    @if($poster)
                        <div class="absolute inset-0 bg-cover bg-center opacity-40"
                             style="background-image: url('{{ $poster }}')"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-noir-950/95 to-noir-950/40"></div>
                    @endif

                    {{-- Small checkmark overlay --}}
                    @if($done && !$freeSquare)
                        <div class="absolute top-1 right-1 z-20">
                             <svg class="w-3 h-3 text-gold-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    @endif

                    <div class="relative z-10">
                        <p class="text-[9px] font-bold leading-tight uppercase tracking-tight
                            {{ $done ? 'text-gold-200' : ($freeSquare ? 'text-gold-400' : 'text-gold-600') }}">
                            {{ $goal->title }}
                        </p>
                        @if($done && $goal->showing)
                            <p class="text-gold-800 text-[7px] mt-0.5 truncate max-w-full opacity-60">
                                {{ $goal->showing->movie->title }}
                            </p>
                        @endif
                    </div>
                </button>
            @endforeach
        </div>
    @endif
</div>
