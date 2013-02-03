<?php

class Butterfly_Theme extends Butterfly_Db_Abstract
{

    /**
     *
     */
    protected $_tableName = 'theme';

    /**
     *
     */
    protected $_fields = array(
        'theme_name' => array(),
        'theme_description' => array(),
        'id_site' => array(),
        'date_creation' => array(),
        'date_update' => array(),
    );

    /**
     *
     */
    protected $_pk = array('id_theme' => null);

    /**
     *
     */
    private $_css = array();

    /**
     *
     */
    private $_xml;

    /**
     *
     */
    private $_js = array();

    /**
     *
     */
    private $_widgets = array();

    /**
     *
     */
    private $_template = null;


    protected static function _getClass()
    {
        return __CLASS__;
    }

    /**
     *
     * Load the current theme from the data base
     *
     */
    public static function loadCurrent($idSite)
    {
        $theme = self::_fetchOne('Butterfly_Theme', 'id_site = :id_site AND theme_current', array('id_site' => $idSite), $additionnals = '');

        return $theme;
    }

    /**
     *
     * Parse the xml if exists and get the css, js and areas. if the
     * file does not exists, an exception is thrown
     *
     */
    public function parseXml()
    {
        $config = Butterfly_Config_Ini::load(CONFIG_FILE, APPLICATION_ENV);
        if (empty($this->_xml)) {
            if (is_file($config->themes_path . '/' . $this->theme_name . '/' . $this->theme_name . '.xml')) {
                $this->_xml = new Butterfly_Xml($config->themes_path . '/' . $this->theme_name . '/' . $this->theme_name . '.xml');
                $this->_css = $this->getcss();
                $this->_js = $this->getjs();
            }
            else {
                throw new Butterfly_Theme_Exception('Xml theme description not found');
            }
        }
    }

    /**
     *
     * Get the list of css files of the theme
     *
     */
    public function getCss()
    {
        if (empty($this->_css)) {
            $nbCss = count($this->_xml->getXml()->css);
            for ($c = 0 ; $c < $nbCss ; $c++) {
                $this->_css[] = $this->theme_name . '/' . (string)$this->_xml->getXml()->css[$c]->attributes()->src;
            }
        }
        return $this->_css;
    }

    /**
     *
     * Get the list of js files of the theme
     *
     */
    public function getJs()
    {
        if (empty($this->_js)) {
            $nbJs = count($this->_xml->getXml()->js);
            for ($j = 0 ; $j < $nbJs ; $j++) {
                $this->_js[] = $this->theme_name . '/' . (string)$this->_xml->getXml()->js[$j]->attributes()->src;
            }
        }
        return $this->_js;
    }

    /**
     *
     *
     */
    public function getWidgets()
    {
        if (empty($this->_widgets)) {
            $nbAreas = count($this->_xml->getXml()->area);
            for ($a = 0 ; $a < $nbAreas ; $a++) {
                $this->_widgets[ (string) $this->_xml->getXml()->area[$a]->attributes()->name ] = array();
                $nbWidgets = count($this->_xml->getXml()->area[$a]);
                for ($p = 0 ; $p < $nbWidgets ; $p++) {
                    $this->_widgets[ (string) $this->_xml->getXml()->area[$a]->attributes()->name ][] = array(
                        'name' => (string) $this->_xml->getXml()->area[$a]->widget[$p]->attributes()->name,
                        'needAuth' => (string) $this->_xml->getXml()->area[$a]->widget[$p]->attributes()->needAuth
                    );
                }
            }
        }
        return $this->_widgets;
    }

    /**
     *
     * return the template of the theme
     *
     */
    public function getTemplate()
    {
        if (empty($this->_template)) {
            $this->_template = (string)$this->_xml->getXml()->attributes()->template;
        }
        return $this->_template;
    }

    /**
     *
     * Return the name of the theme
     *
     */
    public function getName()
    {
        return $this->theme_name;
    }
}
