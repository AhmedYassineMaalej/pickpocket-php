<?php

namespace App\Entities;

class Category
{
    public $id;
    public $name;

    public function __construct($id, $name)
    {
        $this->name = $name;
        $this->id = $id;
    }
}
