<?php

class Butterfly_View_Breadcrumb
{
    private $_crumbs = array();

    private static $_instance;

    private function __construct()
    {

    }

    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    public function assemble($crumbs)
    {
        if (is_array($crumbs)) {
            $this->_crumbs = $crumbs;
        }
        else {
            throw new Butterfly_View_Exception('The crumbs must be an associative array');
        }
    }

    public function render()
    {
        echo '<ul class="breadcrumb">';
        echo '<li><a href="' . SITE_URL . '">Accueil</a></li>';
        foreach ($this->_crumbs as $label => $url) {
            //if current crumb is not a "default", display it
            if (preg_match('/^' . DEFAULT_MODULE . 'module|' . DEFAULT_ACTION . 'action$/', strtolower($label)) == 0) {
            //if (! in_array($label, array(DEFAULT_MODULE, DEFAULT_ACTION))) {
                $label = str_replace('Module', '', str_replace('Action', '', $label));
                echo '<li class="separator">>></li>';
                echo '<li><a href="' . $url . '">' . $label . '</a></li>';
            }
        }
        echo '</ul>';
    }
}

