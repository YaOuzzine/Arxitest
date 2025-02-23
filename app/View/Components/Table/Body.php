<?php

namespace App\View\Components\Table;

use Illuminate\View\Component;

class Body extends Component
{
    public function __construct(
        public $documents
    ) {}

    public function render()
    {
        return view('components.table.body');
    }
}
