<?php

/**
 *
 * Class of the layout of the site.
 * The layout is a template where each view is displayed (module and widgets)
 *
 */
class Butterfly_Layout extends Butterfly_View
{

    /**
     *
     * @var array $_render if true, the page will be fully displayed, if
     * false, only the module will be
     *
     * @access private
     *
     */
    private $_render = true;

    /**
     *
     * @var array $_widgets List of the layout's widgets
     *
     * @access private
     *
     */
    private $_widgets;

    /**
     * @var View $_main View of the Module
     *
     * @access private
     *
     */
    private $_main;

    /**
     * @var array $_cssFiles List of the layout's css files
     *
     * @access private
     *
     */
    private $_cssFiles = null;

    /**
     * @var array $_secondaryCssFiles second list of the layout's css files
     *
     * @access private
     *
     */
    private $_secondaryCssFiles = array();

    /**
     * @var array $_jsFiles List of the layout's js files
     *
     * @access private
     *
     */
    private $_jsFiles = null;

    /**
     * @var array $_secondaryJsFiles second list of the layout's css files
     *
     * @access private
     *
     */
    private $_secondaryJsFiles = array();

    /**
     *
     * Setter of the main content of the layout (the view of the module)
     *
     * @param $content
     * @access public
     *
     */
    public function setMainContent($content){
        $this->_main = $content;
    }

    /**
     *
     * Getter of the content
     *
     * @access public
     *
     */
    public function getMainContent(){
        return $this->_main;
    }

    /**
     *
     * Setter of the layout's widgets
     *
     * @param $plugins
     * @access public
     *
     */
    public function setWidgets($plugins){
        $this->_widgets = $plugins;
    }

    /**
     *
     * Getter of the layout's widgets
     *
     * @access public
     *
     */
    public function getAllWidgets(){
        return $this->_widgets;
    }

    /**
     *
     * Getter of the layout's widgets
     * Return just the widgets of the selected area
     *
     * @access public
     * @param $area
     *
     */
    public function getAllWidgetsFromArea($area){
        return $this->_widgets[$area];
    }

    /**
     *
     * Setter of the js files
     *
     * @param $js
     * @access public
     *
     */
    public function setJs($js)
    {
        $this->_jsFiles = $js;
    }

    /**
     *
     * Add a script sheet which is not in the theme
     * eg : a sheet just for the current page
     *
     */
    public function addSecondaryScriptSheet($sheetName)
    {
        $this->_secondaryJsFiles[] = $sheetName;
    }

    /**
     *
     * Getter of the js files
     *
     * @access public
     *
     */
    public function getJs()
    {
        return $this->_jsFiles;
    }

    /**
     *
     * Getter of the secondary js files
     *
     * @access public
     *
     */
    public function getSecondaryJs()
    {
        return $this->_secondaryJsFiles;
    }

    /**
     *
     * Setter of the css files
     *
     * @param $css
     * @access public
     *
     */
    public function setCss($css)
    {
        $this->_cssFiles = $css;
    }

    /**
     *
     * Getter of the secondary js files
     *
     * @access public
     *
     */
    public function getSecondaryCss()
    {
        return $this->_secondaryCssFiles;
    }

    /**
     *
     * Add a style sheet which is not in the theme
     * eg : a sheet just for the current page
     *
     */
    public function addSecondaryStyleSheet($sheetName)
    {
        $this->_secondaryCssFiles[] = $sheetName;
    }

    /**
     *
     * Getter of the css files
     *
     * @access p$ublic
     *
     */
    public function getCss()
    {
        return $this->_cssFiles;
    }

    public function displayStyleSheets($allSheets)
    {
        if ($allSheets) {
            $array = array_merge($this->_cssFiles, $this->_secondaryCssFiles);
        }
        else {
            $array = $this->_secondaryCssFiles;
        }

        foreach ($array as $cssFile) {
            echo '<link href="' . URL_BASE_PATH . '/styles/' . $cssFile . '" rel="stylesheet" type="text/css" />
';
        }
    }

    public function displayJsScripts($allScripts)
    {
        if ($allScripts) {
            $array = array_merge($this->_jsFiles, $this->_secondaryJsFiles);
        }
        else {
            $array = $this->_secondaryJsFiles;
        }

        foreach ($array as $jsFile) {
            echo '<script type="text/javascript" src="' . URL_BASE_PATH . '/js/' . $jsFile . '"></script>
';
        }
    }

    public function noRender()
    {
        $this->_render = false;
    }

    public function getRender()
    {
        return $this->_render;
    }
}
