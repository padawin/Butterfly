<?php

/**
 *
 * Class to use the configuration parameters set in the website's configuration file
 *
 * @author Ghislain Rodrigues <ghislain.rodrigues@hotmail.fr>
 *
 */
class Butterfly_Config
{

    protected static $_config = array();

    /**
     *
     * Load a config section. If the section is not given, all the config is returned
     *
     * @param string $config required section
     *
     */
    public function __construct($configFile = '', $section = '')
    {
        if (count(self::$_config) == 0 || self::$_config[$configFile] == null) {
            if ($configFile == '') {
                throw new Exception('Config file not specified');
            }

            if (!is_file($configFile)) {
                throw new Butterfly_Config_Exception('Config file not found');
            }

            self::$_config[$configFile] = parse_ini_file($configFile, true);
        }

        if ($section != '') {
            return self::$_config[$configFile][$section];
        }
        else {
            return self::$_config[$configFile];
        }
    }
}
