<?php

class Butterfly_Form_Element_List Extends Butterfly_Form_Element
{

    protected $_name = '';

    protected $_id = '';

    protected $_value = '';

    protected $_list = array();

    public function render()
    {
        $attributes = '';
        foreach ($this->_attributes as $name => $value) {
            $attributes .= $name . '=' . $value . ' ';
        }
        echo '<select name="' . $this->_name . '" id="' . $this->_id . '" ' . $attributes . '>';

        foreach ($this->_list as $id => $value) {
            echo '<option value="' . $id . '" ' . ($this->_value == $id ? 'selected="selected"' : '') . '>' . $value . '</option>';
        }

        echo '</select>';
    }

    public function __construct($name, $id, $value, $list, $attributes = array())
    {
        $this->_name = $name;
        $this->_id = $id;
        $this->_value = $value;
        $this->_list = $list;
        parent::__construct($attributes);
    }
}
