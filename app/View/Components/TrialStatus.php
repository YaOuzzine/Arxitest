<?php

namespace App\View\Components;

use Illuminate\View\Component;

class TrialStatus extends Component
{
    public $daysLeft;
    public $progressPercentage;

    public function __construct()
    {
        $this->daysLeft = 9;
        $this->progressPercentage = 70;
    }

    public function render()
    {
        return view('components.trial-status');
    }
}
