<?php

class Butterfly_Form_Element_Decorator_Label extends Butterfly_Form_Element_Decorator
{
	private $_for;

	private $_text;

	public function __construct(Butterfly_Form_Element $element, $for, $text, $error, $attributes = array())
	{
		if ($for == null || $text == null) {
			throw new Exception('The attribute "for" and the text of the label are required');
		}
		$this->_for = $for;
		$this->_text = $text;
		$this->_error = $error;
		parent::__construct($element, $attributes);
	}

	public function preRender()
	{
		$attributes = '';
		foreach ($this->_attributes as $name => $value) {
			$attributes .= $name . '=' . $value . ' ';
		}
		echo '
		<label for="' . $this->_for . '" ' . $attributes . '>' . $this->_text . ' : </label>';
	}

	public function postRender()
	{
		echo $this->_error;
	}
}
