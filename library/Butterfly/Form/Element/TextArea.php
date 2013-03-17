<?php

class Butterfly_Form_Element_TextArea Extends Butterfly_Form_Element
{

	protected $_name = '';

	protected $_id = '';

	protected $_value = '';

	public function __construct($name, $id, $value, $attributes = array())
	{
		$this->_name = $name;
		$this->_id = $id;
		$this->_value = $value;
		parent::__construct($attributes);
	}

	public function render()
	{
		$attributes = '';
		foreach ($this->_attributes as $name => $value) {
			$attributes .= $name . '=' . $value . ' ';
		}
		echo '<textarea name="' . $this->_name . '" id="' . $this->_id . '" ' . $attributes . '>' . $this->_value . '</textarea>';
	}
}
