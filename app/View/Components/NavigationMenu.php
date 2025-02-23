<?php

namespace App\View\Components;

use Illuminate\View\Component;

class NavigationMenu extends Component
{
    public $items;

    public function __construct()
    {
        $this->items = [
            [
                'name' => 'Home',
                'icon' => 'home',
                'route' => '/',
                'active' => request()->is('/')
            ],
            [
                'name' => 'Inbox',
                'icon' => 'inbox',
                'route' => '/inbox',
                'active' => request()->is('inbox')
            ],
            [
                'name' => 'Reports',
                'icon' => 'reports',
                'route' => '/reports',
                'active' => request()->is('reports')
            ],
        ];
    }

    public function render()
    {
        return view('components.navigation-menu');
    }
}
