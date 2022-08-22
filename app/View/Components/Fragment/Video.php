<?php

namespace App\View\Components\Fragment;

use Illuminate\View\Component;

class Video extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public string $videoUrl) {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render() {
        return <<<'blade'
    <video src="{{ $videoUrl }}" controls>
blade;;
    }
}
