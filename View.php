<?php

class Butterfly_View
{

    /**
     *
     */
    private $_file;

    /**
     *
     */
    private $_layout;

    /**
     *
     */
    private $_vars = array();


    private $_viewBase = '';

    /**
     *
     * Construct
     *
     * @param Layout $layout
     *
     */
    public function __construct (&$layout = null)
    {
        if ($layout != null) {
            $this->_layout = $layout;
        }
    }

    /**
     *
     * Return the view's layout
     *
     * @return Layout
     *
     */
    public function getLayout()
    {
        return $this->_layout;
    }

    /**
     *
     */
    public function getFile()
    {
        return $this->_file;
    }

    /**
     *
     */
    public function setFile($file)
    {
        $this->_file = $file;
    }

    /**
     *
     */
    public function setBase($path)
    {
        $this->_viewBase = $path;
    }

    /**
     *
     */
    public function getBase()
    {
        return $this->_viewBase;
    }

    /**
     *
     */
    public function __set($key, $val)
    {
        if ($this->_layout != null) {
            $this->_layout->$key = $val;
        }
        //for the layout itself
        else {
            $this->_vars[$key] = $val;
        }
        return $this;
    }

    /**
     *
     */
    public function __get($key)
    {
        if ($this->_layout != null) {
            return $this->_layout->$key;
        }
        elseif (isset($this->_vars[$key])) {
            return $this->_vars[$key];
        }
        else {
            return null;
        }
    }

    /**
     *
     * clean output values
     *
     * @param string $var
     * @return string
     *
     */
    public function escape ($var)
    {
        return htmlspecialchars ($var);
    }

    /**
     *
     * render the view's file
     *
     * @param mix $file
     * @return string
     *
     */
    public function render ()
    {
        $extensions = array('php', 'html');
        for ($i = 0 ; $i < count($extensions) ; $i ++) {
            if (is_file($this->_viewBase . $this->_file . '.' . $extensions[$i])) {
                ob_start();
                require ($this->_viewBase . $this->_file . '.' . $extensions[$i]);
                return ob_get_clean();
            }
        }

        throw new Butterfly_View_Exception('View file not found : ' . $this->_viewBase . $this->_file . '.*');
    }

    /*
     *
     * Method to create an url
     *
     * @TODO Algorithme a revoir !
     * mettre parametre dans un array,
     * enlever les parametres vides ou default
     * implode array...
     *
     */
    public function url($module = null, $action = null, $get = array())
    {
        $config = Butterfly_Config_Ini::load(CONFIG_FILE, APPLICATION_ENV);
        if (empty($module)) {
            $module = $config->default_module;
        }
        if (empty($action)) {
            $action = $config->default_action;
        }

        if (is_array($get)) {
            $getString = array();
            foreach ($get as $param => $value) {
                $getString[] = $param . '/' . $value;
            }
            $getString = implode('/', $getString);
        }
        elseif (!is_string($get)) {
            throw new View_Exception('The $get arg must be a string or an associative array');
        }
        else {
            $getString = $get;
        }

        $url = '/';

        if ($module != '' && $module != $config->default_module) {
            $url .= $module;
            if ($action != '' && $action != $config->default_action) {
                $url .= '/' . $action;
            }
        }

        if ($url == '/') {
            return $url;
        }
        else {
            return strtolower($this->strToUrl(rtrim($url, '/') . ($getString != '' ? '/' . $getString : '' )));
        }
    }

    public function display($string)
    {
        echo $string;
    }

    public function strToUrl($string)
    {
        $normalizeChars = array(
            'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
            'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
            'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U',
            'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a',
            'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
            'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u',
            'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f'
        );

        $string     =    str_replace(' ', '-', $string);
        $string     =    str_replace('--', '-', $string);

        return strtr($string, $normalizeChars);
    }

}
