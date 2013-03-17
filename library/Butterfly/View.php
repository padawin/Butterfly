<?php

class Butterfly_View
{

	/**
	 *
	 */
	private $_file;

	/**
	 *
	 */
	private $_layout;

	/**
	 *
	 */
	private $_vars = array();


	private $_viewBase = '';

	/**
	 *
	 * Construct
	 *
	 * @param Layout $layout
	 *
	 */
	public function __construct (&$layout = null)
	{
		if ($layout != null) {
			$this->_layout = $layout;
		}
	}

	/**
	 *
	 * Return the view's layout
	 *
	 * @return Layout
	 *
	 */
	public function getLayout()
	{
		return $this->_layout;
	}

	/**
	 *
	 */
	public function getFile()
	{
		return $this->_file;
	}

	/**
	 *
	 * Set the view's layout
	 *
	 * @return Layout
	 *
	 */
	public function setLayout(&$layout)
	{
		$this->_layout = $layout;
	}

	/**
	 *
	 */
	public function setFile($file)
	{
		$this->_file = $file;
	}

	/**
	 *
	 */
	public function setBase($path)
	{
		$this->_viewBase = $path;
	}

	/**
	 *
	 */
	public function getBase()
	{
		return $this->_viewBase;
	}

	/**
	 *
	 */
	public function __set($key, $val)
	{
		if ($this->_layout != null) {
			$this->_layout->$key = $val;
		}
		//for the layout itself
		else {
			$this->_vars[$key] = $val;
		}
		return $this;
	}

	/**
	 *
	 */
	public function __get($key)
	{
		if ($this->_layout != null) {
			return $this->_layout->$key;
		}
		elseif (isset($this->_vars[$key])) {
			return $this->_vars[$key];
		}
		else {
			return null;
		}
	}

	public function __isset($key)
	{
		return $this->$key != NULL;
	}

	/**
	 *
	 * clean output values
	 *
	 * @param string $var
	 * @return string
	 *
	 */
	public function escape ($var)
	{
		return htmlspecialchars ($var);
	}

	/**
	 *
	 * render the view's file
	 *
	 * @param mix $file
	 * @return string
	 *
	 */
	public function render ()
	{
		$extensions = array('php', 'html');
		for ($i = 0 ; $i < count($extensions) ; $i ++) {
			if (is_file($this->_viewBase . $this->_file . '.' . $extensions[$i])) {
				ob_start();
				require ($this->_viewBase . $this->_file . '.' . $extensions[$i]);
				return ob_get_clean();
			}
		}

		throw new Butterfly_View_Exception('View file not found : ' . $this->_viewBase . $this->_file . '.*');
	}

	/*
	 *
	 * Method to create an url
	 *
	 * @TODO Algorithme a revoir !
	 * mettre parametre dans un array,
	 * enlever les parametres vides ou default
	 * implode array...
	 *
	 */
	public function url($module = null, $action = null, $get = array())
	{
		if (is_array($get)) {
			$get = http_build_query($get);
		}
		elseif (!is_string($get)) {
			throw new View_Exception('The $get arg must be a string or an associative array');
		}

		return '?' . http_build_query(
			array('module' => $module, 'action' => $action)
		) . (!empty($get) ? "&{$get}" : '');
	}

	public function display($string)
	{
		echo $string;
	}

	public static function strToUrl($string)
	{
		return urlencode($string);
	}
}
