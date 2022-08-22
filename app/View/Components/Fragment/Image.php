<?php

namespace App\View\Components\Fragment;

use Illuminate\View\Component;

class Image extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct( public string $imageUrl ) {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render() {
        return <<<'blade'
    <img src="{{ $imageUrl }}">
blade;
    }
}
