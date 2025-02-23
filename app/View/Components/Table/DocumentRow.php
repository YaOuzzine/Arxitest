<?php

namespace App\View\Components\Table;

use Illuminate\View\Component;

class DocumentRow extends Component
{
    public function __construct(
        public $document
    ) {}

    public function render()
    {
        return view('components.table.document-row');
    }
}
