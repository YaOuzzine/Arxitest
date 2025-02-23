<?php

namespace App\View\Components;

use Illuminate\View\Component;

class DocumentsTable extends Component
{
    public function __construct(
        public $documents
    ) {}

    public function render()
    {
        return view('components.documents-table');
    }
}
