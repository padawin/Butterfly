<?php

class Butterfly_Form_Element_Text Extends Butterfly_Form_Element_Input
{
    public function __construct($name, $id, $value, $attributes = array())
    {
        parent::__construct($name, $id, $value, 'text', $attributes);
    }
}
