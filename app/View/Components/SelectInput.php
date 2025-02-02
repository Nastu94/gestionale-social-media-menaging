<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SelectInput extends Component
{
    public $name;
    public $id;
    public $options;
    public $required;

    public function __construct($name, $id, $options, $required = false)
    {
        $this->name = $name;
        $this->id = $id;
        $this->options = $options;
        $this->required = $required;
    }

    public function render()
    {
        return view('components.select-input');
    }
}
