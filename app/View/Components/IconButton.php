<?php

namespace App\View\Components;

use Illuminate\View\Component;

class IconButton extends Component
{
    public $icon;

    public function __construct(string $icon)
    {
        $this->icon = $icon;
    }

    public function render()
    {
        return view('components.icon-button');
    }
}
