<?php
/**
 *
 * Class to use the request parameters (GET And POST)
 *
 * @TODO secure params using :
 *
 * htmlspecialchars($_GET['test'])
 *
 * When a param is requested, check if it exists in a class var, if it
 * exists, it is secured, use it, else, get the param, secure it and
 * store it in the class var
 *
 */


class Butterfly_Http_Request
{

    private $_post;

    private $_get;

    private $_params = array();

    private $_dispatched = false;

    private static $_instance = null;

    private function __construct()
    {

    }

    public static function getInstance()
    {
        if (empty(self::$_instance)) {
            self::$_instance = new self;
            self::$_instance->_init();
        }

        return self::$_instance;
    }

    protected function _init()
    {
        $this->_get = $_GET;
        $this->_post = $_POST;
    }

    public function setParam($name, $value)
    {
        $this->_params[$name] = $value;
    }

    /**
     * Return the param named $paramName
     * @param $paramName name of the requested parameter
     */
    public function getParam($paramName, $default = null)
    {
        if (isset($this->_params[$paramName])) {
            return htmlspecialchars($this->_params[$paramName]);
        }
        elseif (isset($this->_get[$paramName])) {
            return htmlspecialchars($this->_get[$paramName]);
        }
        elseif (isset($this->_post[$paramName])) {
            return htmlspecialchars($this->_post[$paramName]);
        }
        else {
            return $default;
        }
    }

    public function getAllParams()
    {
        return array_merge($this->_post, $this->_get, $this->_params);
    }

    public function hasPostParam($param)
    {
        return !empty($this->_post[$param]);
    }

    public function getPostParam($param)
    {
        if ($this->hasPostParam($param)) {
            return $this->_post[$param];
        }
        else {
            return null;
        }
    }

    public function forward($module, $action, $params = array())
    {
        $this->_dispatched = false;

        $this->_params = array_merge($this->_params, $params);
        $this->_params['module'] = $module;
        $this->_params['action'] = $action;

    }

    public function isDispatched()
    {
        return $this->_dispatched;
    }

    public function setDispatch($dispatch)
    {
        $this->_dispatched = $dispatch;
    }

    public function redirect($url = '')
    {
        header( "Location: {$url}");
        exit;
    }
}
