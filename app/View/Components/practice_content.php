<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class practice_content extends Component
{
    public $practices;
    /**
     * Create a new component instance.
     */
    public function __construct($practices)
    {
        $this->practices = $practices;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.practice_content');
    }
}
