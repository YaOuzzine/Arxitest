<?php

namespace App\View\Components;

use Illuminate\View\Component;

class MenuButton extends Component
{
    public function __construct()
    {
    }

    public function render()
    {
        return view('components.menu-button');
    }
}
