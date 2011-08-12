<?php

class Butterfly_Form_Element_Decorator_Table extends Butterfly_Form_Element_Decorator_Multiple
{

    public function __construct($attributes = array())
    {
    }

    public function render()
    {
        $attributes = '';
        foreach ($this->_attributes as $n => $value) {
            $attributes .= $n . '=' . $value . ' ';
        }
        $nbElements = count($this->_elements);

        echo '<table ' . $attributes . '>';
        for ($i = 0 ; $i < $nbElements ; $i ++) {
            echo '
            <tr>
                <td>';
                $this->_elements[$i]->preRender();
                echo '</td>
                <td>';
                $this->_elements[$i]->renderElement();
                echo '</td>
                <td>';
                $this->_elements[$i]->postRender();
                echo '</td>
            </tr>';
        }
        echo '</table>';
    }
}
