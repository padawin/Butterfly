<?php

/**
 *
 * Class to use the configuration parameters set in the website's configuration file
 *
 * @author Ghislain Rodrigues <ghislain.rodrigues@hotmail.fr>
 *
 */
class Butterfly_Config_Ini extends Butterfly_Config
{

    public static function load($configFile = '', $section = '')
    {
        if ($configFile == '') {
            throw new Exception('Config file not specified');
        }

        if (!is_file($configFile)) {
            throw new Butterfly_Config_Exception('Config file not found');
        }

        $config = (Object) parse_ini_file($configFile, true);
        $config = self::_parseSections($config);

        if ($section != null) {
            return $config->{$section};
        }
        else {
            return $config;
        }
    }

    /**
     *
     * Load a config section. If the section is not given, all the config is returned
     *
     * @param string $config required section
     *
     */
    protected function __construct($content = null, &$parent = null)
    {
        if (!empty($content)) {
            $this->_elements = $content;
        }

        if (!empty($parent)) {
            $this->_parent = $parent;
        }
    }

    protected static function _parseSections($config)
    {
        $newConfig = new self;

        foreach ($config as $configName => $content) {

            if (!is_array($content)) {
                $newConfig->{$configName} = $content;
            }
            else {
                $configName = explode(':', $configName);
                if (count($configName) > 1) {
                    $parent = $newConfig->{trim($configName[1])};
                }
                else {
                    $parent = null;
                }

                //@TODO parse fields
                $contentCleaned = array();
                foreach ($content as $field => $value) {
                    $contentCleaned = array_merge_recursive($contentCleaned, self::_parseField($field, $value));
                }

                $content = (Object) $contentCleaned;

                $newConfig->{trim($configName[0])} = new self($content, $parent);
            }
        }

        return $newConfig;
    }

    /**
     *
     * convert ini fields toto.tata=foo into object toto with field tata which have 'foo' as value
     *
     */
    protected static function _parseField($fieldName, $fieldValue)
    {
        $fieldName = explode('.', $fieldName);
        $depth = count($fieldName);
        for ($i = $depth - 1 ; $i >= 0 ; $i--) {
            $field = array();
            $field[$fieldName[$i]] = $fieldValue;
            $fieldValue = $field;
        }

        return $field;
    }
}
