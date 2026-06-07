<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class notechart extends Component
{
    public $target;

    public function __construct($target)
    {
        $this->target = $target;
    }

    public function render(): View|Closure|string
    {
        return view('components.notechart');
    }
}
