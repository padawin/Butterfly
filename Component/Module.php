<?php
/**
 *
 * Modules class
 *
 */
class Butterfly_Component_Module extends Butterfly_Component
{

    public function init()
    {}

    /**
     *
     * Alias to do a forward in the module
     *
     */
    protected function forward($module = '', $action = '', $complement = array())
    {
        Butterfly_FrontController::getInstance()->forward($module, $action, $complement);
    }

    /**
     *
     * Alias to do a redirect in the module
     *
     */
    protected function redirect($module = '', $action = '', $complement = array())
    {
        Butterfly_FrontController::getInstance()->redirect($module, $action, $complement);
    }

    /**
     *
     * Add a style sheet to the page
     *
     */
    protected function addStyleSheet($sheetName)
    {
        $this->_view->getLayout()->addSecondaryStyleSheet($sheetName);
    }

    /**
     *
     * Add a js script sheet to the page
     *
     */
    protected function addScriptSheet($sheetName)
    {
        $this->_view->getLayout()->addSecondaryScriptSheet($sheetName);
    }

    /**
     *
     * Alias for the Front Controller addWidget method
     *
     */
    public function addWidget($widget, $area, $needAuth = false)
    {
        Butterfly_FrontController::getInstance()->addWidget($widget, $area, $needAuth);
    }
}
