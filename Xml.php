<?php

/**
 *
 * Class to manage XML files
 *
 */
class Butterfly_Xml
{
    /**
     *
     * @var array
     *
     */
    private $_xml = array();

    /**
     *
     * @var string
     *
     */
    private $_file = '';

    /**
     *
     * @var SimpleXml
     *
     */
    private $_xmlFile = '';

    /**
     *
     */
    public function __construct($file)
    {
        $this->_file = $file;
        $this->_xmlFile = simplexml_load_file($this->_file);
    }

    /**
     *
     * Getter of the xml
     *
     */
    public function getXml()
    {
        return $this->_xmlFile;
    }
}
