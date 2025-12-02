<?php

namespace App\Livewire;

use Illuminate\View\View;
use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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

    public function flip(): void
    {
        $currentUser = Auth::user();

        if (! $currentUser) {
            return;
        }

        if ($currentUser->is_current_payer) {
            return;
        }

        // Clear current payer and set the authenticated user as payer
        User::where('is_current_payer', true)->update(['is_current_payer' => false]);
        $currentUser->update(['is_current_payer' => true]);

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