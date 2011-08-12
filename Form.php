<?php

/**
 *
 * Class to create HTML forms
 *
 * @author Ghislain Rodrigues <ghislain.rodrigues@hotmail.fr>
 *
 */
class Butterfly_Form
{

    /**
     *
     * List of the fields of the form
     *
     * @var array
     * @access private
     *
     */
    private $_elements;

    /**
     *
     * Method of the form.
     * Can be post or get
     *
     * @var sting
     * @access private
     *
     */
    private $_method;

    /**
     *
     * Url where the form goes on submit
     *
     * @var string
     * @access private
     *
     */
    private $_action;

    /**
     *
     * Attributes of the HTML tag form
     *
     * @var array
     * @access private
     *
     */
    private $_attributes = array();

    /**
     *
     * Construct of the class, set the method, the action and the attributes
     *
     * @param string $method forms's method
     * @param string $action form's action
     * @param array $attributes form's attributes
     *
     */
    public function __construct($method = 'POST', $action = '', $attributes = array())
    {
        $this->_elements = array();
        $this->_method = $method;
        $this->_action = $action;
        $this->_attributes = $attributes;
    }

    /**
     *
     * Add an element in the form. Such as an input, text area, decorator...
     *
     * @param Form_Element $element element to add
     *
     */
    public function addElement(Butterfly_Form_Element $element)
    {
        $this->_elements[] = $element;
        return $this;
    }

    /**
     *
     * Build the form and display it with its elements
     *
     */
    public function render()
    {
        $attributes = '';
        foreach ($this->_attributes as $name => $value) {
            $attributes .= $name . '=' . $value . ' ';
        }
        echo '<form action="' . $this->_action . '" method="' . $this->_method . '" ' . $attributes . '>';

        $nbElements = count($this->_elements);
        for ($e = 0 ; $e < $nbElements ; $e++) {
            $this->_elements[$e]->render();
        }

        echo "\n</form>";
    }
}
