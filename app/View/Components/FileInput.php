<?php

namespace App\View\Components;

use Illuminate\View\Component;

class FileInput extends Component
{
    public $name;
    public $id;
    public $required;

    public function __construct($name, $id, $required = false)
    {
        $this->name = $name;
        $this->id = $id;
        $this->required = $required;
    }

    public function render()
    {
        return view('components.file-input');
    }
}
