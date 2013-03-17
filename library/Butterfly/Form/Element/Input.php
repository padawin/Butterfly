<?php

abstract class Butterfly_Form_Element_Input extends Butterfly_Form_Element
{

	protected $_name = '';

	protected $_type = '';

	protected $_id = '';

	protected $_value = '';

	public function __construct($name, $id, $value, $type, $attributes = array())
	{
		$this->_name = $name;
		$this->_id = $id;
		$this->_value = $value;
		$this->_type = $type;
		parent::__construct($attributes);
	}

	public function render()
	{
		$attributes = '';
		foreach ($this->_attributes as $name => $value) {
			$attributes .= $name . '=' . $value . ' ';
		}
		echo '<input type="' . $this->_type . '" name="' . $this->_name . '" id="' . $this->_id . '" value="' . $this->_value . '" ' . $attributes . ' />';
	}

	public function setName($name)
	{
		$this->_name = $name;
	}

	public function setType($type)
	{
		$this->_type = $type;
	}

	public function setId($id)
	{
		$this->_id = $id;
	}

	public function setValue($value)
	{
		$this->_value = $value;
	}
	public function getName()
	{
		return $this->_name;
	}

	public function getType()
	{
		return $this->_type;
	}

	public function getId()
	{
		return $this->_id;
	}

	public function getValue()
	{
		return $this->_value;
	}
}
