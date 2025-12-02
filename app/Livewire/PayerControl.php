<?php

namespace App\Livewire;

use Illuminate\View\View;
use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PayerControl extends Component
{
    public function flip(): void
    {
        $currentUser = Auth::user();

        if ($currentUser->is_current_payer) {
            return;
        }

        User::where('is_current_payer', true)->update(['is_current_payer' => false]);
        $currentUser->update(['is_current_payer' => true]);

        $this->dispatch('turn-flipped');
    }

    public function render(): View
    {
        $currentUser = Auth::user();
        $payer = User::where('is_current_payer', true)->first();
        $canFlip = $currentUser && $payer && $currentUser->id !== $payer->id;

        return view('livewire.payer-control', [
            'payerName' => $payer ? $payer->name : 'No One Set',
            'canFlip' => $canFlip
        ]);
    }
}