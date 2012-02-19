<?php

/**
 * Class to manage the acl resources
 * An acl resource in a part of a road which define a restricted area.
 *
 */
class Butterfly_Acl_Resource extends Butterfly_Db_Abstract
{
    protected $_tableName = 'acl_resource';

    protected $_fields = array(
        'id_site' => array(),
        'acl_resource_module' => array(),
        'acl_resource_action' => array(),
        'date_creation' => array(),
        'date_update' => array(),
    );

    protected $_pk = array('id_acl_resource' => null);

    protected static function _getClass()
    {
        return __CLASS__;
    }

    /**
     * Method to search if a resource match to the requested resource
     *
     */
    public static function loadFromRequest($module, $action, $idSite)
    {
        $resource = new self;
        $resource = $resource->_fetchOne('Butterfly_Acl_Resource',
            "id_site = :id_site AND (
            acl_resource_module = LOWER(:module) AND acl_resource_action = '' OR
            acl_resource_module = LOWER(:module) AND acl_resource_action = LOWER(:action) )",
            array(':module' => $module, ':action' => $action, 'id_site' => $idSite)
        );

        return $resource;
    }

    /**
     *
     * Load all the acl resources
     *
     */
    public static function loadAll($where = '', $values = array(), $additionnals = '', $join = array())
    {
        $resource = new self;
        return $resource->_fetch('Butterfly_Acl_Resource', $where, $values, $additionnals, $join);
    }


}
