<?php

/**
 *
 * Factory to create a form element (text, password, checkbox...) from
 * the element name
 *
 */
class Butterfly_Form_Element_Factory
{

	/**
	 *
	 * list of each types
	 *
	 */
	const TYPE_TEXT = 1;
	const TYPE_PASSWORD = 2;
	const TYPE_CHECKBOX = 3;
	const TYPE_RADIO = 4;
	const TYPE_TEXTAREA = 5;
	const TYPE_LIST = 6;
	const TYPE_BOOLEAN = 7;
	const TYPE_FILE = 8;


	/**
	 *
	 * Method to create the element from the type
	 *
	 */
	public static function get($elementType, $name, $value, $additionals = array())
	{
		$element = null;
		switch($elementType) {
			default:
			case self::TYPE_TEXT:
				$element = new Butterfly_Form_Element_Text($name, $name, $value);
				break;
			case self::TYPE_TEXTAREA:
				$element = new Butterfly_Form_Element_TextArea($name, $name, $value, array('rows' => 7, 'cols' => 50));
				break;
			case self::TYPE_PASSWORD:
				$element = new Butterfly_Form_Element_Password($name, $name, $value);
				break;
			case self::TYPE_CHECKBOX:
				$element = new Butterfly_Form_Element_CheckBox($name, $name, $value);
				break;
			case self::TYPE_RADIO:
				$element = new Butterfly_Form_Element_Radio($name, $name, $value);
				break;
			case self::TYPE_BOOLEAN:
				$element = new Butterfly_Form_Element_Boolean($name, $name, $value);
				break;
			case self::TYPE_LIST:
				$element = new Butterfly_Form_Element_List($name, $name, $value, $additionals['values']);
				break;
		}

		return $element;
	}
}
