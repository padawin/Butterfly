<?php

abstract class Butterfly_Form_Element_Decorator_Multiple extends Butterfly_Form_Element
{
	protected $_elements = array();

	public function __construct(Array $elements)
	{
		$this->_elements = $elements;
		parent::__construct();
	}

	public function addElement($element)
	{
		$this->_elements[] = $element;
	}

	public function render()
	{
		$nbElements = count($this->_elements);
		for ($i = 0 ; $i < $nbElements ; $i++) {
			$this->_elements[$i]->render();
		}
		unset($nbElements);
	}
}
