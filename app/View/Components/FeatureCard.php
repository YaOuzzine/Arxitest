<?php

namespace App\View\Components;

use Illuminate\View\Component;

class FeatureCard extends Component
{
    public function __construct(
        public string $title,
        public string $description,
        public string $icon,
        public string $color = 'gray'
    ) {}

    public function render()
    {
        return view('components.feature-card');
    }
}
