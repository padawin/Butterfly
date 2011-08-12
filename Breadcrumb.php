<?php

class Butterfly_Breadcrumb
{
    private $_crumbs = array();

    public function render()
    {
        echo '<ul id="breadcrumb">' . PHP_EOL;
        $nbCrumbs = count($this->_crumbs);
        $class = 'first-crumb';
        for ($i = 0 ; $i < $nbCrumbs ; $i++) {
            if ($i != 0) {
                $class = '';
            }
            echo '<li class="' . $class . '">' . PHP_EOL;
            if ($i < $nbCrumbs - 1) {
                echo '<a href="' . $this->_crumbs[$i]['url'] . '">' . $this->_crumbs[$i]['label'] . '</a>' . PHP_EOL;
            }
            else {
                echo $this->_crumbs[$i]['label'] . PHP_EOL;
            }
            echo '</li>';
        }
        echo '</ul>' . PHP_EOL;
    }

    public function addCrumb($label, $url)
    {
        $this->_crumbs[] = array(
            'url' => $url,
            'label' => $label
        );
    }
}
