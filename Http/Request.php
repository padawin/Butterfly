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


abstract class Butterfly_Http_Request
{

    private static $_post;

    private static $_get;

    private static $_params = array();

    private function __construct()
    {

    }

    public static function setParam($name, $value)
    {
        self::$_params[$name] = $value;
    }

    /**
     * Return the param named $paramName
     * @param $paramName name of the requested parameter
     */
    public static function getParam($paramName, $default = null)
    {
        if (isset($_GET[$paramName])) {
            return htmlspecialchars($_GET[$paramName]);
        }
        elseif (isset($_POST[$paramName])) {
            return htmlspecialchars($_POST[$paramName]);
        }
        elseif (isset(self::$_params[$paramName])) {
            return htmlspecialchars(self::$_params[$paramName]);
        }
        else {
            return $default;
        }
    }

    /**
     * Return the POST param $paramName is it exists, else null
     */
    public static function getPostParam($paramName)
    {
        return (isset($_POST[$paramName]) ? htmlspecialchars($_POST[$paramName]) : null);
    }

    /**
     * Return all the POST params
     * @TODO : test
     */
    public static function getAllPostParams()
    {
        if (self::$_post == null) {
            self::$_post = array();
            foreach ($_POST as $key => $value) {
                self::$_post[$key] = htmlspecialchars($value);
            }
        }
        return self::$_post;
    }

    /**
     * Return the GET param $paramName is it exists, else null
     */
    public static function getGetParam($paramName)
    {
        return (isset($_GET[$paramName]) ? htmlspecialchars($_GET[$paramName]) : null);
    }

    /**
     * Return true is the GET param $paramName exists, else false
     */
    public static function hasGetParam($paramName)
    {
        return isset($_GET[$paramName]);
    }

    /**
     * Return true is the GET param $paramName exists, else false
     */
    public static function hasPostParam($paramName)
    {
        return isset($_POST[$paramName]);
    }

    /**
     * Return all the GET params
     */
    public static function getAllGetParams()
    {
        return $_GET;
    }
}
