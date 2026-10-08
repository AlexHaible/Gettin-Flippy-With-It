<?php

namespace App\Livewire;

use App\Actions\FlipSnackPayer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class PayerControl extends Component
{
    public string $payerName;

    private function loadPayerState(): void
    {
        $payer = User::where('is_current_payer', true)->first();
        $this->payerName = $payer ? $payer->username : 'No One Set';
    }

    public function mount(): void
    {
        $this->loadPayerState();
    }

    public function flip(FlipSnackPayer $flipSnackPayer): void
    {
        $currentUser = Auth::user();

        if (! $currentUser || ! $flipSnackPayer($currentUser)) {
            return;
        }

        // Reload state from the database
        $this->loadPayerState();

        // Force this component to re-render itself
        $this->dispatch('$refresh');

        // Keep your existing browser event in case the front-end listens for it
        $this->dispatch('turn-flipped');
    }

    public function render(): View
    {
        $currentUser = Auth::user();
        $payer = User::where('is_current_payer', true)->first();
        $canFlip = $currentUser && $payer && $currentUser->id !== $payer->id;

        return view('livewire.payer-control', [
            'payerName' => $this->payerName,
            'canFlip' => $canFlip,
        ]);
    }

    public function fetchData(): void
    {
        $this->loadPayerState();
    }
}
