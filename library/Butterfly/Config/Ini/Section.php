<?php

class Butterfly_Config_Ini_Section
{
	protected $_parent;
	protected $_elements;

	public function __construct($elements, $parent = null)
	{
		$this->_elements = $elements;
		$this->_parent = $parent;
	}

	public function __get($key)
	{
		return $this->_parent->{$key};
	}
}
