<?php

class Butterfly_Scaffold
{
    public static function createForm(Butterfly_Db_Abstract $object)
    {
        $form = new Butterfly_Form();

        $class = Butterfly_Factory::cleanNamespace(get_class($object));
        $fields = $this->_fields;
        $table = new Butterfly_Form_Element_Decorator_Table();

        foreach ($fields as $name => $infos) {
            if (isset($infos['type'])) {
                $type = $infos['type'];
                $element = new Butterfly_Form_Element_Decorator_Label(
                    Butterfly_Form_Element_Factory::get(
                        $type,
                        $name,
                        $this->$name
                    ),
                    $name,
                    $infos['desc'],
                    isset($errors[$name]) ? '<span class="error">' . $errors[$name] . '</span>' : ''
                );
                $table->addElement($element);
            }
        }

        foreach ($this->_fks as $name => $infos) {
            if (isset($infos['desc'])) {
                $elts = $infos['class']::loadAllActives();
                $nbElements = count($elts);

                $elements = array('' => 'aucun');
                for ($i = 0 ; $i < $nbElements ; $i ++) {
                    $elements[$elts[$i]->{$elts[$i]->getPkName()}] = $elts[$i]->{$elts[$i]->getTableName() . '_label'};
                }

                $element = new Butterfly_Form_Element_Decorator_Label(
                    Butterfly_Form_Element_Factory::get(
                        Butterfly_Form_Element_Factory::TYPE_LIST,
                        $name,
                        $this->$name,
                        array(
                            'values' => $elements
                        )
                    ),
                    $name,
                    $infos['desc'],
                    isset($errors[$name]) ? '<span class="error">' . $errors[$name] . '</span>' : ''
                );
                $table->addElement($element);
            }
        }

        foreach ($this->_hasAndBelongsToMany as $class => $infos) {
            if (isset($infos['desc'])) {
                $elts = $class::loadAllActives();
                $nbElements = count($elts);

                $elements = array();
                //foreach items
                for ($i = 0 ; $i < $nbElements ; $i ++) {
                    //Create a label which contains a checkbox
                    $element = new Butterfly_Form_Element_Decorator_Label(
                        new Butterfly_Form_Element_CheckBox(
                            $class . '[]',
                            strtolower($class) . '_' . $elts[$i]->getPkValue(),
                            $elts[$i]->getPkValue(),
                            (!empty($infos['values']) && in_array($elts[$i], $infos['values']) ? array('checked' => 'checked') : array())
                        ),
                        strtolower($class) . '_' . $elts[$i]->getPkValue(),
                        $elts[$i]->{$elts[$i]->getTableName() . '_label'},
                        isset($errors[strtolower($class) . '_' . $elts[$i]->getPkValue()]) ? '<span class="error">' . $errors[strtolower($class) . '_' . $elts[$i]->getPkValue()] . '</span>' : ''
                    );
                    $elements[] = $element;
                }

                //create a multiplecheckbox set
                $elements = new Butterfly_Form_Element_CheckBox_Multiple($elements);

                //create a label to the set
                $element = new Butterfly_Form_Element_Decorator_Label(
                    $elements,
                    strtolower($class),
                    $infos['desc'],
                    ''
                );

                //all all in the form
                $table->addElement($element);
            }
        }

        $table->addElement(new Butterfly_Form_Element_Submit('submit_db_form', 'submit_db_form', 'Submit'));
        $form->addElement($table);

        unset($fields);
        unset($table);
        unset($element);
        unset($type);

        return $form;
    }
}
