<?php

namespace App\View\Components;

use Illuminate\View\Component;

class GettingStartedSteps extends Component
{
    public $steps;
    public $completedSteps;

    public function __construct()
    {
        $this->steps = [
            'Sign-up to PandaDoc',
            'Create document from a template',
            'Send and sign a document',
            'Create a template',
            'Explore integrations'
        ];

        $this->completedSteps = 2; // First two steps completed
    }

    public function render()
    {
        return view('components.getting-started-steps');
    }
}
