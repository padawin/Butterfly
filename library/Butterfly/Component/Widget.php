<?php

class Butterfly_Component_Widget extends Butterfly_Component
{
	private $_name = '';

	/**
	 *
	 * Construct of the class
	 *
	 * @param Layout $layout Layout of the page
	 * @param String $name Widget name
	 * @access public
	 *
	 */
	public function __construct($layout, $name = '')
	{
		$this->_name = $name;
		parent::__construct($layout);
	}
	public function render()
	{
		echo '<div class="widget ' . $this->_name . '">';
		parent::render();
		echo '</div>';
	}
}
