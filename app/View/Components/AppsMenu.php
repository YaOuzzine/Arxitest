<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AppsMenu extends Component
{
    public $apps;

    public function __construct()
    {
        $this->apps = [
            [
                'name' => 'Documents',
                'icon' => 'document',
                'route' => '/documents'
            ],
            [
                'name' => 'Automations',
                'icon' => 'automation',
                'route' => '/automations'
            ],
        ];
    }

    public function render()
    {
        return view('components.apps-menu');
    }
}
