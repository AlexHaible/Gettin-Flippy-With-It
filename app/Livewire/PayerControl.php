<?php

namespace App\Livewire;

use App\Models\Showing;
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

    public function flip(): void
    {
        $currentUser = Auth::user();

        if (! $currentUser) {
            return;
        }

        if ($currentUser->is_current_payer) {
            return;
        }

        // Identify who was the payer before this flip using the DB state
        $previousPayer = User::where('is_current_payer', true)->first();

        // Update the most relevant showing to attribute it to the previous payer
        if ($previousPayer) {
            // Find closest showing to now() to handle "just before" or "after" flips
            $past = Showing::where('start_time', '<', now())->latest('start_time')->first();
            $upcoming = Showing::where('start_time', '>=', now())->oldest('start_time')->first();

            $targetShowing = null;

            if ($past && $upcoming) {
                // Check which is closer
                $diffPast = now()->diffInMinutes($past->start_time, true);
                $diffUpcoming = now()->diffInMinutes($upcoming->start_time, true);
                $targetShowing = ($diffUpcoming < $diffPast) ? $upcoming : $past;
            } elseif ($past) {
                $targetShowing = $past;
            } elseif ($upcoming) {
                $targetShowing = $upcoming;
            }

            // Only update if we found a relevant showing.
            // We assume explicit user intent "Flip" = "Confirm Payment for current event".
            if ($targetShowing) {
                $targetShowing->update([
                    'popcorn_payer_id' => $previousPayer->id,
                    'soda_payer_id' => $previousPayer->id,
                ]);
            }

            // Clear current payer
            $previousPayer->update(['is_current_payer' => false]);
        }

        // Set the authenticated user as payer
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
