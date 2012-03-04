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
     * Widgets That must not be displayed
     *
     * @var Array of String
     * @access private
     *
     */
    private $_widgetsToIgnore = array();

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
     * Instance of Butterfly_Http_Request
     *
     */
    private $_request;

    /**
     *
     * Construct of the class
     * Set the class attribute $_layout with a new Layout
     *
     * @access private
     *
     */
    protected function __construct(){
        $this->_layout = Butterfly_Factory::create('Layout');
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
            self::$_instance = new static;
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
        $requestClass = Butterfly_Factory::getClass('Http_Request');
        $this->_setRequest($requestClass::getInstance());
        unset($requestClass);

        $loop = 0;
        while (!$this->_request->isDispatched()) {

            $this->_request->setDispatch(true);

            try {
                if ($loop == 0) {
                    $configClass = Butterfly_Factory::getClass('Config_Ini');
                    $this->_setConfig($configClass::load(CONFIG_FILE, APPLICATION_ENV));

                    $this->_setIncludePath();

                    //load and execute each plugins
                    $this->_predispatchPlugins();
                }

                $this->_moduleParam = ucfirst($this->_request->getParam($this->_config->module_param, ucfirst($this->_config->default_module)));
                $this->_actionParam = $this->_request->getParam( $this->_config->action_param, $this->_config->default_action );

                //disable layout render if ajax
                if ($this->_request->getParam('ajax')) {
                    $this->_layout->noRender();
                }

                //load and execute each widgets
                $this->_loadPlugins();

                //load and execute the module
                $this->_loadModule();

                if ($this->_request->isDispatched()) {
                    //load the theme and get styles and script sheets
                    $this->_loadTheme();

                    if ($this->_layout->getRender()) {
                        //load and execute each widget
                        $this->_loadWidgets();
                    }

                    //load the template
                    $this->_loadTemplate();

                    $this->render();
                }
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

            $loop++;
        }
    }

    protected function _setRequest(Butterfly_Http_Request $request)
    {
        $this->_request = $request;
    }

    protected function _setConfig(Butterfly_Config $config)
    {
        $this->_config = $config;
    }

    /**
     *
     * Add the modules path, plugins path and widgets path to the include
     * path.
     *
     * @access private
     *
     */
    protected function _setIncludePath()
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
     * predispatch them.
     *
     * @access private
     *
     */
    protected function _predispatchPlugins()
    {
        $this->_executePluginStep('predispatch');
    }

    /**
     *
     * Method to get the plugins list from the config file, load and
     * execute them.
     *
     * @access private
     *
     */
    protected function _loadPlugins()
    {
        $this->_executePluginStep('execute');
    }

    /**
     * common process to run all the plugins.
     *
     * @param string $step name of the method to run
     */
    protected function _executePluginStep($step)
    {
        $pluginsList = $this->_config->plugins_list;
        if (!empty($pluginsList)) {
            $pluginsList = explode(',', $pluginsList);
            $nbPlugins = count($pluginsList);
            for ($p = 0 ; $p < $nbPlugins ; $p++) {
                $plugin = Butterfly_Factory::getClass(trim($pluginsList[$p]) . '_Plugin');
            if (in_array('Butterfly_Plugin', class_parents($plugin))) {
                if (method_exists($plugin, $step)) {
                    $plugin::$step();
                }
            }
            else {
                throw new Butterfly_Exception('The plugins must extend Butterfly_Plugin class');
            }
        }
    }

    /**
     *
     * Load the module from the request.
     * If the module exists, the controller name is defined.
     *     If the controller exists, it is instanciated and init() is launched.
     *          Then, if the action is defined, the action method is launched if it exists
     *          and the view file is defined if it wasn't.
     *          If the action isn't defined, the default action is launched
     *     Else A default module is created and the view is defined
     * Else a exception is thrown
     *
     * @access private
     *
     */
    protected function _loadModule()
    {
        //check if module name and action name are only [A-Za-z]
        if (!preg_match('#^[\w-_]+$#', $this->_moduleParam)) {
            throw new Butterfly_Component_Module_Exception('The module name is not correct');
        }
        if (!preg_match('#^[\w-_]+$#', $this->_actionParam)) {
            throw new Butterfly_Component_Module_Exception('The action name is not correct');
        }

        if (!is_dir($this->_config->modules_path . '/' . $this->_moduleParam)) {
            throw new Butterfly_Component_Module_Exception('The module does not exist');
        }

        //the controller exists
        try {
            $controllerName = Butterfly_Factory::getClass($this->_moduleParam . '_Controller');
        }
        //the class does not exist, create generic controller
        catch (Exception $e) {
            $controllerName = Butterfly_Factory::getClass('Component_Module');
        }
        $this->_setModule(new $controllerName($this->_layout));
        $this->_module->init();

        $this->_module->setViewBase($this->_config->modules_path . '/' . $this->_moduleParam . '/views/');

        if (!empty($this->_actionParam)) {
            $action = preg_replace('/[-_\s]/', '', strtolower($this->_actionParam)) . 'Action';
            $view = strtolower($this->_actionParam);
        }
        else {
            $action = $this->_config->default_action . 'Action';
            $view = $this->_config->default_action;
        }

        if (method_exists($this->_module, $action)) {
            $this->_module->$action();
        }

        //The view can have been setted in the init or action
        if (!$this->_module->hasViewFile()) {
            $this->_module->setView($view);
        }

        //at this point a view MUST be defined
        if (!$this->_module->viewExists()) {
            throw new Butterfly_View_Exception('View file not found : ' . $this->_module->getView()->getFile());
        }
    }

    protected function _setModule(Butterfly_Component_Module $module)
    {
        $this->_module = $module;
    }

    /**
     *
     * Load the current theme from the cookies if it exists, else load the current theme
     * from the database
     *
     * @access private
     *
     */
    protected function _loadTheme()
    {
        $themeClass = Butterfly_Factory::getClass('Theme');

        //if current theme is in cookies
        if (!empty($_COOKIE['current_theme_' . $this->_config->id_site])) {
            $method = 'loadById';
            $id = $_COOKIE['current_theme'];
        }
        else {
            $method = 'loadCurrent';
            $id = $this->_config->id_site;
        }

        $this->_setTheme($themeClass::$method($id));

        if ($this->_theme) {
            $this->_theme->parseXml();
        }
        else {
            throw new Exception('No Theme found');
        }
    }

    protected function _setTheme(Butterfly_Theme $theme)
    {
        $this->_theme = $theme;
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
    protected function _loadWidgets()
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
                if (isset($this->_widgetsToIgnore[$areaName]) && in_array($widgets[$i]['name'], $this->_widgetsToIgnore[$areaName])) {
                    unset($w[$areaName][$i]);
                    continue;
                }

                if (
                    empty($widgets[$i]['needAuth']) ||
                    (
                        $widgets[$i]['needAuth'] &&
                        call_user_func(array(Butterfly_Factory::getClass('Acl_User'), 'getConnectedUser')) != null)
                ) {
                    if (!is_file($this->_config->widgets_path . '/' . $widgets[$i]['name'] . '/view.php')) {
                        throw new Butterfly_Component_Widget_Exception('The view file for the widget ' . $widgets[$i]['name'] . ' does not exist');
                    }
                    else {
                        try {
                            $widgetName = Butterfly_Factory::getClass($widgets[$i]['name'] . '_WidgetController');
                        }
                        catch (Exception $e) {
                            $widgetName = Butterfly_Factory::getClass('Component_Widget');
                        }

                        $w[$areaName][$i] = new $widgetName($this->_layout, $widgets[$i]['name']);
                        $this->_buildWidget($w[$areaName][$i], $widgets[$i]['name']);
                    }
                }
                else {
                    unset($w[$areaName][$i]);
                }
            }
        }
        $this->_widgets = $w;
    }

    protected function _buildWidget(Butterfly_Component_Widget &$widget, $name)
    {
        $widget->setViewBase($this->_config->widgets_path . '/' . $name . '/');
        $widget->setView('view');

        if (method_exists($widget, 'build')) {
            $widget->build();
        }
    }

    /**
     *
     * Load the template from the current Theme, define the css and js files,
     * the widgets and the module
     *
     * @access private
     *
     */
    protected function _loadTemplate()
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
    public function render()
    {
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
    public function forward($module, $action = '', $params = array())
    {
        $this->_widgets = array();
        $this->_request->forward(
            preg_replace('/[\s-_]/', '', $module),
            preg_replace('/[\s-_]/', '', $action),
            $params
        );
    }

    /**
     *
     * Set the module name, action name and complementary parameter
     *
     */
    public function setRoad($module, $action = '')
    {
        $this->_moduleParam = ucfirst($module);
        $this->_actionParam = $action;
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

    public function removeWidget($widgetName, $area)
    {
        if (!isset($this->_widgetsToIgnore[$area])) {
            $this->_widgetsToIgnore[$area] = array();
        }

        $this->_widgetsToIgnore[$area][] = $widgetName;

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
