<?php

class Butterfly_Form_Element_Boolean extends Butterfly_Form_Element_Decorator_Multiple
{

    public function __construct($name, $id, $value, $attributes = array())
    {
        parent::__construct(array(
            new Butterfly_Form_Element_Decorator_Label(
                new Butterfly_Form_Element_Radio($name, $id . '-yes', 1, ($value == 1 || $value === true ? array('checked' => 'checked') : array())),
                $id . '-yes',
                'oui',
                '',
                $attributes
            ),
            new Butterfly_Form_Element_Decorator_Label(
                new Butterfly_Form_Element_Radio($name, $id . '-no', 0, ($value == 0 || $value === false ? array('checked' => 'checked') : array())),
                $id . '-no',
                'non',
                '',
                $attributes
            ),
            ));
    }

    public function render()
    {
        parent::render();
    }
}
