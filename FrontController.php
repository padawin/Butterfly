<?php

/**
 *
 * Class to load the plugins, the module, the theme and the widget from the request
 * Implements Singleton Design Pattern
 *
 * @author Ghislain Rodrigues <ghislain.rodrigues@hotmail.fr>
 *
 */
class Butterfly_FrontController
{

    /**
     *
     * Module name, from the Http request
     *
     * @access private
     *
     */
    private $_moduleParam;

    /**
     *
     * Action name, from the Http request
     *
     * @access private
     *
     */
    private $_actionParam;

    /**
     *
     * Complementary parameter, from the Http request
     *
     * @access private
     *
     */
    private $_complementParam;

    /**
     *
     * Template of the page, in which the module and the widgets will be displayed
     *
     * @var Layout
     * @access private
     *
     */
    private $_layout;


    /**
     *
     * Instance of the Front controller
     *
     * @var FrontController
     * @access private
     *
     */
    private static $_instance = null;

    /**
     *
     * Theme of the page. A theme has a collection of css and js files and a template
     *
     * @var Theme
     * @access private
     *
     */
    private $_theme;

    /**
     *
     * Module of the current page.
     *
     * @var Component_Module
     * @access private
     *
     */
    private $_module;

    /**
     *
     * Widgets of the current Theme
     *
     * @var Array of Component_Widget
     * @access private
     *
     */
    private $_widgets = array();

    /**
     *
     * Widgets of the current Theme
     *
     * @var Array of Component_Widget
     * @access private
     *
     */
    private $_ajax = null;

    /**
     *
     * Instance of Butterfly_Config
     *
     */
    private $_config;

    /**
     *
     * Construct of the class
     * Set the class attribute $_layout with a new Layout
     *
     * @access private
     *
     */
    private function __construct(){
        $this->_layout = new Butterfly_Layout();
    }

    /**
     *
     * Return the instance of the Butterfly_FrontController.
     * Implementation of the Singleton Design Pattern
     *
     * @return Butterfly_FrontController
     * @access public
     * @static
     *
     */
    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     *
     * Return the front Controller's layout
     *
     */
    public function getLayout()
    {
        return $this->_layout;
    }

    /**
     *
     * Entry Point of the website
     * Will :
     * - load the constants defined in the config file,
     * - load and execute the plugins,
     * - load the module from the request
     * - if the request is not ajax, load the theme and the theme's
     * widgets
     * - and load the template
     *
     * @access public
     *
     */
    public function run()
    {
        try {
            $this->_config = Butterfly_Config_Ini::load(CONFIG_FILE, APPLICATION_ENV);
            $this->_setIncludePath();

            //load and execute each widgets
            $this->_predispatchPlugins();

            $this->_moduleParam = Butterfly_Http_Request::getParam( $this->_config->module_param, ucfirst($this->_config->default_module) );
            $this->_actionParam = Butterfly_Http_Request::getParam( $this->_config->action_param, $this->_config->default_action );

            if (Butterfly_Http_Request::getParam('ajax')) {
                $this->_layout->noRender();
            }

            //load and execute each widgets
            $this->_loadPlugins();
            //load and execute the module
            $this->_loadModule();

            //load the theme and get styles and script sheets
            $this->_loadTheme();

            if ($this->_layout->getRender()) {
                //load and execute each widget
                $this->_loadWidgets();
            }

            //load the template
            $this->_loadTemplate();
        }
        catch (Butterfly_Component_Module_Exception $te)
        {
            $this->forward('error', 'notfound');
        }
        catch (Butterfly_Component_Plugin_Exception $te)
        {
            Butterfly_Session::set('error', 'An exception occured during the widgets loading : <br />' . $te->getMessage());
            $this->forward('error', 'other');
        }
        catch (Butterfly_Theme_Exception $te)
        {
            Butterfly_Session::set('error', 'An exception occured during the theme loading : <br />' . $te->getMessage());
            $this->forward('error', 'other');
        }
        catch (Butterfly_Config_Exception $te)
        {
            Butterfly_Session::set('error', 'An exception occured during the config loading : <br />' . $te->getMessage());
            $this->forward('error', 'other');
        }
        catch (Exception $te)
        {
            Butterfly_Session::set('error', 'An exception occured : <br />' . $te->getMessage());
            $this->forward('error', 'other');
        }
    }

    /**
     *
     * Add the modules path, plugins path and widgets path to the include
     * path.
     *
     * @access private
     *
     */
    private function _setIncludePath()
    {
        set_include_path(
        $this->_config->modules_path . PATH_SEPARATOR .
        $this->_config->plugins_path . PATH_SEPARATOR .
        $this->_config->widgets_path . PATH_SEPARATOR .
        get_include_path());
    }

    /**
     *
     * Method to get the plugins list from the config file, load and
     * execute them.
     *
     * @access private
     *
     */
    private function _loadPlugins()
    {
        $pluginsList = $this->_config->plugins_list;
        if (!empty($pluginsList)) {
            $pluginsList = explode(',', $pluginsList);
            $nbPlugins = count($pluginsList);
            for ($p = 0 ; $p < $nbPlugins ; $p++) {
                $plugin = trim($pluginsList[$p]) . '_Plugin';
                if (method_exists($plugin, 'execute')) {
                    $plugin::execute();
                }
            }
        }
    }

    /**
     *
     * Method to get the plugins list from the config file, load and
     * predispatch them.
     *
     * @access private
     *
     */
    private function _predispatchPlugins()
    {
        $pluginsList = $this->_config->plugins_list;
        if (!empty($pluginsList)) {
            $pluginsList = explode(',', $pluginsList);
            $nbPlugins = count($pluginsList);
            for ($p = 0 ; $p < $nbPlugins ; $p++) {
                $plugin = trim($pluginsList[$p]) . '_Plugin';
                if (method_exists($plugin, 'predispatch')) {
                    $plugin::predispatch();
                }
            }
        }
    }

    /**
     *
     * Load the module from the request.
     * If the module exists, the controller name is defined.
     *     If the controller exists, it is instanciated and the function init is launched.
     *          Then, if the action is defined, the action method is launched if it exists
     *          and the view file is defined if it wasn't.
     *          If the action isn't defined, the default action is launched
     *     Else A default module is created and the view is defined
     * Else a exception is thrown
     *
     * @access private
     *
     */
    private function _loadModule()
    {
        //check if module name and action name are only [A-Za-z]

        $controllerName = $this->_moduleParam . '_Controller';

        //the controller exists
        if (is_file($this->_config->modules_path . '/' . $this->_moduleParam . '/Controller.php')) {
            $this->_module = new $controllerName($this->_layout);
            $this->_module->ajax = !$this->_layout->getRender();
            $this->_module->init();

            if (!empty($this->_actionParam)) {
                $action = preg_replace('/[-_\s]/', '', strtolower($this->_actionParam)) . 'Action';

                if (method_exists($this->_module, $action)) {
                    $this->_module->$action();
                }

                $this->_module->setViewBase($this->_config->modules_path . '/' . $this->_moduleParam . '/views/');
                if (!$this->_module->hasViewFile()) {
                    $this->_module->setView(strtolower($this->_actionParam));
                }
            }
            else {
                $defaultAction = $this->_config->default_action . 'Action';
                if (method_exists($this->_module, $this->_config->default_action . 'Action')) {
                    $this->_module->$defaultAction();
                }
                $this->_module->setViewBase($this->_config->modules_path . '/' . $this->_moduleParam . '/views/');
                if (!$this->_module->hasViewFile()) {
                    $this->_module->setView($this->_config->default_action);
                }
            }
        }
        // the controller does not exist
        else {
            $this->_module = new Butterfly_Component_Module($this->_layout);

            $this->_module->setViewBase($this->_config->modules_path . '/' . $this->_moduleParam . '/views/');
            if(!empty($this->_actionParam)){
                $this->_module->setView(strtolower($this->_actionParam));
            }
            else {
                $this->_module->setView($this->_config->default_action);
            }
        }

        //at this point a view MUST be defined
    }

    /**
     *
     * Load the current theme from the cookies if it exists, else load the current theme
     * from the database
     *
     * @access private
     *
     */
    private function _loadTheme()
    {
        //if current theme is in cookies
        if(!empty($_COOKIE['current_theme_' . $this->_config->id_site])){
            $this->_theme = Butterfly_Theme::loadById($_COOKIE['current_theme']);
        }
        else {
            $this->_theme = Butterfly_Theme::loadCurrent($this->_config->id_site);
        }

        if ($this->_theme) {
            $this->_theme->parseXml();
        }
        else {
            throw new Exception('No Theme found');
        }
    }

    /**
     *
     * Load the widgets from the theme.
     * For each widget, if its view file does not exists, an exception
     * is thrown. Else, the widget is built and the view is defined
     *
     * @access private
     *
     */
    private function _loadWidgets()
    {
        $w = $this->_theme->getWidgets();

        foreach ($this->_widgets as $areaName => $widgets) {
            if (!isset($w[$areaName])) {
                $w[$areaName] = array();
            }
            $w[$areaName] = array_merge($w[$areaName], $widgets);

        }

        foreach ($w as $areaName => $widgets) {
            $nbWidget = count($widgets);
            for ($i = 0 ; $i < $nbWidget ; $i++) {
                if ($widgets[$i]['needAuth'] != true || ($widgets[$i]['needAuth'] == true && Butterfly_Acl_User::getConnectedUser() != null)) {
                    $widgetName = $widgets[$i]['name'] . '_WidgetController';
                    if (!is_file($this->_config->widgets_path . '/' . $widgets[$i]['name'] . '/view.php')) {
                        throw new Butterfly_Component_Widget_Exception('The view file for the widget ' . $widgets[$i]['name'] . ' does not exist');
                    }
                    else {
                        if (is_file($this->_config->widgets_path . '/' . $widgets[$i]['name'] . '/WidgetController.php')) {
                            $w[$areaName][$i] = new $widgetName($this->_layout, $widgets[$i]['name']);
                            $w[$areaName][$i]->setViewBase($this->_config->widgets_path . '/' . $widgets[$i]['name'] . '/');
                            $w[$areaName][$i]->setView('view');
                            $w[$areaName][$i]->build();
                        }
                        else {
                            $w[$areaName][$i] = new Butterfly_Component_Widget($this->_layout, $widgets[$i]['name']);
                            $w[$areaName][$i]->setViewBase($this->_config->widgets_path . '/' . $widgets[$i]['name'] . '/');
                            $w[$areaName][$i]->setView('view');
                        }
                    }
                }
                else {
                    unset($w[$areaName][$i]);
                }
            }
        }
        $this->_widgets = $w;
    }

    /**
     *
     * Load the template from the current Theme, define the css and js files,
     * the widgets and the module
     *
     * @access private
     *
     */
    private function _loadTemplate()
    {
        $this->_layout->setBase($this->_config->themes_path . '/' . $this->_theme->getName() . '/');
        $this->_layout->setFile('template');
        $this->_layout->setCss($this->_theme->getCss());
        $this->_layout->setJs($this->_theme->getJs());

        //dynamically add css and js for the current page
        $currentJs = $this->_theme->theme_name . '/' . strtolower($this->_moduleParam) . '/' . preg_replace('/[-_\s]/','',strtolower($this->_actionParam)) . '.js';
        $currentCss = $this->_theme->theme_name . '/' . strtolower($this->_moduleParam) . '/' . preg_replace('/[-_\s]/','',strtolower($this->_actionParam)) . '.css';

        if (is_file($this->_config->public_path . $this->_config->js_public_path . '/' . $currentJs)) {
            $this->_layout->addSecondaryScriptSheet($currentJs);
        }
        if (is_file($this->_config->public_path . $this->_config->css_public_path . '/' . $currentCss)) {
            $this->_layout->addSecondaryStyleSheet($currentCss);
        }

        if ($this->_layout->getRender()) {
            $this->_layout->setMainContent($this->_module);
            $this->_layout->setWidgets($this->_widgets);
        }
    }

    /**
     *
     * Render the layout with its content
     *
     * @access public
     *
     */
    public function render(){
        if ($this->_layout->getRender()) {
            print($this->_layout->render());
        }
        else {
            $this->_layout->displayStyleSheets(false);
            $this->_layout->displayJsScripts(false);
            print($this->_module->render());
        }
    }

    /**
     *
     * Method to execute a forward on an other url without reload the page
     *
     * @TODO merge this method with run method to avoid duplicated code
     * @access public
     *
     */
    public function forward($module, $action = '', $complement = '')
    {
        $this->setRoad($module, $action, $complement);

        try {
            //load and execute the module
            $this->_loadModule();

            if ($this->_layout->getRender()) {
                //load the theme and get styles and script sheets
                $this->_loadTheme();
                //load and execute each widget
                $this->_loadWidgets();
                //load the template
                $this->_loadTemplate();
            }

            $this->render();
        }
        catch (Butterfly_Component_Module_Exception $te)
        {
            $this->forward('error', 'notfound');
        }
        catch (Butterfly_Component_Plugin_Exception $te)
        {
            echo 'An exception occured during the widgets loading : <br />';
            echo $te->getMessage();
        }
        catch (Butterfly_View_Exception $te)
        {
            echo 'An exception occured during the view loading : <br />';
            echo $te->getMessage();
        }
        catch (Butterfly_Theme_Exception $te)
        {
            echo 'An exception occured during the theme loading : <br />';
            echo $te->getMessage();
        }
        catch (Exception $te)
        {
            echo 'An exception occured : <br />';
            echo $te->getMessage();
        }

        exit(0);
    }

    public function redirect($module = '', $action = '')
    {
        header( 'Location: ' . $this->_layout->url($module, $action));
        exit;
    }

    /**
     *
     * Set the module name, action name and complementary parameter
     *
     */
    public function setRoad($module, $action = '', $complement = '')
    {
        $this->_moduleParam = ucfirst($module);
        $this->_actionParam = $action;
        $this->_complementParam = $complement;
    }

    /**
     *
     * Add a widget to the page in the desired area
     *
     * @param $widgetName
     * @param $area Area where the widget will be displayed
     * @param $needAuth
     *
     */
    public function addWidget($widgetName, $area, $needAuth = false)
    {
        if (!isset($this->_widgets[$area])) {
            $this->_widgets[$area] = array();
        }
        $this->_widgets[$area][] = array(
            'name' => $widgetName,
            'needAuth' => $needAuth
        );
    }

    public function getModule()
    {
        return $this->_moduleParam;
    }

    public function getAction()
    {
        return $this->_actionParam;
    }

    public function getConfig()
    {
        return $this->_config;
    }
}
