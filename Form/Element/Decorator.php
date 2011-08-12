<?php

abstract class Butterfly_Form_Element_Decorator extends Butterfly_Form_Element
{
    protected $_element;

    public function __construct(Butterfly_Form_Element $element, $attributes = array())
    {
        $this->_element = $element;
        parent::__construct($attributes);
    }

    public function renderElement()
    {
        $this->_element->render();
    }
}
