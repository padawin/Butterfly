<?php

class Butterfly_Tracking extends Butterfly_Db_Abstract
{

    /**
     *
     */
    protected $_tableName = 'tracking';

    /**
     *
     */
    protected $_fields = array(
        'tracking_module' => array(),
        'tracking_action' => array(),
        'tracking_tri' => array(),
        'tracking_ip' => array(),
        'date_creation' => array(),
        'date_update' => array(),
    );

    /**
     *
     */
    protected $_pk = array('id_tracking' => null);

    protected static function _getClass()
    {
        return __CLASS__;
    }
}
