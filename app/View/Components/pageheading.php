<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class pageheading extends Component
{

    public string $heading;
    public array $navigation;
    public ?string $description;

    /**
     * Create a new component instance.
     */
    public function __construct(string $heading, array $navigation, ?string $description = null)
    {
        $this->heading = $heading;
        $this->description = $description;
        $this->navigation = $navigation;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.pageheading');
    }
}
