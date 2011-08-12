<?php

class Butterfly_Form_Element
{

    protected $_attributes = array();

    public function __construct($attributes = array())
    {
        $this->_attributes = $attributes;
    }

    public function setAttributes($attributes)
    {
        $this->_attributes = $attributes;
    }

    public function getAttributes()
    {
        return $this->_attributes;
    }

    public function preRender()
    {}

    public function postRender()
    {}

    public function renderElement()
    {
        $this->render();
    }

    public function render()
    {
        $this->preRender();
        $this->renderElement();
        $this->postRender();
    }
}
