<?php

class Butterfly_Acl_Role extends Butterfly_Db_NestedSet
{
	protected $_tableName = 'acl_role';

	protected $_fields = array(
		'acl_role_name' => array(),
		'nestedset_label' => array(),
		'nestedset_left' => array(),
		'nestedset_right' => array(),
		'date_creation' => array(),
		'date_update' => array(),
	);

	protected $_pk = array('id_acl_role' => null);

	protected static function _getClass()
	{
		return __CLASS__;
	}

	public function hasAccessToResource($idResource)
	{
		$stmt = self::getDbAdapter()->prepare('
		SELECT
			id_acl_resource
		FROM
			acl_resource_role rr
			INNER JOIN acl_role r ON
				rr.id_acl_role = r.id_acl_role AND
				nestedset_left >= :left AND nestedset_right <= :right
		WHERE rr.id_acl_resource = :idResource');

		$stmt->execute(array(
			'left' => $this->nestedset_left,
			'right' => $this->nestedset_right,
			'idResource' => $idResource));

		return $stmt->fetch() != false;
	}

	/**
	 *
	 * Load all the acl roles
	 *
	 */
	public static function loadAll($where = '', $values = array(), $additionnals = '', $join = array())
	{
		$role = new self;
		return $role->_fetch('Butterfly_Acl_Role', $where, $values, $additionnals, $join);
	}

	public static function loadByName($name)
	{
		$role = new self;

		return $role->_fetchOne(
			'Butterfly_Acl_Role',
			'acl_role_name = :name',
			array(
				'name' => $name
			)
		);
	}
}
