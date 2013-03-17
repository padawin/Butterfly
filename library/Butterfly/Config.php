<?php

abstract class Butterfly_Config
{

	protected $_elements;
	protected $_parent;

	public function __get($key)
	{
		if (isset($this->_elements->$key)) {
			return $this->_elements->$key;
		}
		elseif ($this->_parent != null) {
			return $this->_parent->{$key};
		}
		else {
			return null;
		}
	}

	public function __set($key, $value)
	{
		if ($this->_elements == null) {
			$this->_elements = new StdClass;
		}
		$this->_elements->$key = $value;
	}
}
