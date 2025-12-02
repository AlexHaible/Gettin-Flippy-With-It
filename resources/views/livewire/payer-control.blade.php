<div class="flex flex-col items-center justify-center min-h-[85vh] text-center relative py-12 px-6 overflow-hidden">

    {{-- 1. BOLD BACKGROUND: "Fan" Pattern (High Contrast) --}}
    <div class="absolute inset-0 deco-pattern-fan opacity-30 pointer-events-none"></div>

    {{-- 2. TOP LABEL --}}
    <div class="relative z-10 mb-16 px-6 py-4 bg-noir-900/75">
        <div class="deco-divider w-56 mx-auto mb-4"></div>
        <div class="deco-text-metallic text-xl uppercase tracking-[0.4em] font-display font-bold text-shadow-gold">
            Current Patron
        </div>
        <div class="deco-divider w-56 mx-auto mt-4"></div>
    </div>

    {{-- 3. THE PLACARD: Explicit Container for the Name --}}
    <div class="relative z-20 w-full max-w-lg flex justify-center">

        @if($canFlip)
            {{-- INTERACTIVE BUTTON (Art Deco Animation) --}}
            {{-- Structure:
                 - Outer Button (group): Handles positioning and inner shadow
                 - Label (span): Handles text color and background fill transition
                 - Ornaments (divs): The animated lines that shrink on hover
            --}}
            <button
                wire:click="flip"
                wire:confirm="Confirm payment? This will switch the turn to YOU."
                wire:key="payer-btn"
                wire:transition.opacity.duration.500ms
                class="group relative min-w-[340px] p-2 text-center uppercase tracking-[0.4em] shadow-[inset_0_0_0_1px_var(--color-gold-500)]"
            >
                {{-- Button Label --}}
                <span class="block bg-transparent px-8 py-6 text-2xl font-display font-bold text-gold-500 transition-colors duration-500 group-hover:bg-gold-500/90 group-hover:text-black">
                    {{ $payerName }}
                </span>

                {{-- Vertical Ornaments (Top & Bottom Lines) --}}
                {{-- Logic: Positioned -2 from top/bottom. Shrink horizontally (scale-x-0) on hover. --}}
                <div class="absolute -top-2 -bottom-2 left-0 right-0 pointer-events-none">
                    <div class="absolute top-0 left-0 right-0 h-px bg-gold-500 transition-transform duration-400 ease-[cubic-bezier(0.54,0.06,0.39,0.96)] group-hover:scale-x-0"></div>
                    <div class="absolute bottom-0 left-0 right-0 h-px bg-gold-500 transition-transform duration-400 ease-[cubic-bezier(0.54,0.06,0.39,0.96)] group-hover:scale-x-0"></div>
                </div>

                {{-- Horizontal Ornaments (Left & Right Lines) --}}
                {{-- Logic: Positioned -2 from left/right. Shrink vertically (scale-y-0) on hover. --}}
                <div class="absolute -left-2 -right-2 top-0 bottom-0 pointer-events-none">
                    <div class="absolute left-0 top-0 bottom-0 w-px bg-gold-500 transition-transform duration-400 ease-[cubic-bezier(0.54,0.06,0.39,0.96)] group-hover:scale-y-0"></div>
                    <div class="absolute right-0 top-0 bottom-0 w-px bg-gold-500 transition-transform duration-400 ease-[cubic-bezier(0.54,0.06,0.39,0.96)] group-hover:scale-y-0"></div>
                </div>

                {{-- Sub-text / Instruction --}}
                <div class="absolute -bottom-12 left-0 right-0 text-[10px] text-gold-600 tracking-widest opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    CLICK TO ROTATE
                </div>
            </button>

        @else
            {{-- STATIC CARD --}}
            <div wire:key="payer-static" wire:transition.opacity.duration.500ms
                 class="relative min-w-[340px] px-8 py-10 bg-noir-900 deco-border-metallic border-2 shadow-2xl flex flex-col items-center"
            >

                <h1 class="text-6xl md:text-7xl font-display font-bold text-gold-500 text-shadow-gold">
                    {{ $payerName }}
                </h1>

                @auth
                    <div class="mt-8 text-gold-700 italic font-serif text-xs tracking-widest uppercase border-t border-gold-900 pt-4 w-full">
                        The Honor is Yours
                    </div>
                @endauth
            </div>
        @endif

    </div>
</div>
