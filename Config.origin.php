<?php

/**
 *
 * Class to use the configuration parameters set in the website's configuration file
 * Implements Singleton Design Pattern
 *
 * @author Ghislain Rodrigues <ghislain.rodrigues@hotmail.fr>
 *
 */
class Butterfly_Config
{

    /**
     *
     * Associative array of the configuration
     *
     * @static array
     * @access private
     *
     */
    static private $_config = null;

    /**
     *
     * Instance of the configuration
     *
     * @access private
     *
     */
    static private $_instance = null;

    /**
     *
     * Construct of the class
     * Load the config file as associative array into $_config
     *
     * @access private
     *
     */
    private function __construct($iniFile)
    {
        if (!is_file($iniFile)) {
            throw new Butterfly_Config_Exception('Config file not found');
        }
        self::$_config = parse_ini_file($iniFile, true);
        /*
        foreach ($parsedIniFile as $sName => $config) {
            $explodedSectionName = explode(':', $sName);
            $section = array();
            //section with subsection
            $nbsect = count($explodedSectionName);
            if ($nbsect > 1) {
                for ($sub = 0 ; $sub < $nbsect ; $sub ++) {

                }
            }
        }
        */
    }

    /**
     *
     * Load a config section. If the section is not given, all the config is returned
     *
     * @param string $config required section
     *
     */
    static public function loadConfig($section = '', $configFile ='')
    {
        if (self::$_instance === null) {
            if ($configFile != '') {
                self::$_instance = new self($configFile);
            }
            else {
                throw new Exception('Config file not specified');
            }
        }
        if ($section != '') {
            return self::$_config[$section];
        }
        else {
            return self::$_config;
        }
    }
}
