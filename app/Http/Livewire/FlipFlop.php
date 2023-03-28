<?php

namespace App\Http\Livewire;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class FlipFlop extends Component
{
    public User $turnToPay;
    public User $paidLast;

    public function render()
    {
        $users = User::all();

        if (count($users) !== 2) {
            return view('livewire.empty');
        }

        foreach ($users as $user) {
            if ($user->canFlip === 1) {
                $this->turnToPay = $user;
            } else {
                $this->paidLast = $user;
            }
        }

        return view('livewire.flip-flop');
    }

    public function flipflop(User $turnToPay, User $paidLast)
    {
        $currentUser = Auth::user();
        if (!$currentUser->canFlip) {
            return false;
        }

        $turnToPay->canFlip = 0;
        $turnToPay->save();
        $paidLast->canFlip = 1;
        $paidLast->save();
    }
}
