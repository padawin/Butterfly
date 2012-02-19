<?php

class Butterfly_Form_Element_Submit Extends Butterfly_Form_Element_Input
{
    public function __construct($name, $id, $value, $attributes = array())
    {
        parent::__construct($name, $id, $value, 'submit', $attributes);
    }
}
