<div class="text-9xl">
    <p class="capitalize" wire:click="flipflop({{ $turnToPay->id }}, {{ $paidLast->id }})">{{ $paidLast->username }}</p>

    <script>
        document.addEventListener('livewire:load', () => {
            setInterval(() => {
                @this.call('fetchData');
            }, 1000); // 1 second
        });
    </script>
</div>
