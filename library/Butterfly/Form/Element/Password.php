<?php

class Butterfly_Form_Element_Password Extends Butterfly_Form_Element_Input
{
    public function __construct($name, $id, $value, $otherAttributes = array())
    {
        parent::__construct($name, $id, $value, 'password', $otherAttributes);
    }
}
