<?php

abstract class Butterfly_Db_Abstract
{

	protected $_fields = array();

	protected $_pk = array();

	protected $_fks = array();

	protected $_hasAndBelongsToMany = array();

	protected static $_db;

	/**
	 *
	 */
	public function __set($key, $value)
	{
		if (isset($this->_fields[$key])) {
			$this->_fields[$key]['value'] = $value;
		}
		elseif (array_key_exists($key, $this->_pk)) {
			$this->_pk[$key]['value'] = $value;
		}
		elseif (isset($this->_fks[$key])) {
			$this->_fks[$key]['value'] = $value;
		}
		elseif (isset($this->_hasAndBelongsToMany[$key])) {
			$this->_hasAndBelongsToMany[$key]['values'] = $value;
		}
	}

	/**
	 *
	 */
	public function __get($key)
	{
		if (isset($this->_fields[$key])) {
			return isset($this->_fields[$key]['value']) ? $this->_fields[$key]['value'] : null;
		}
		elseif (isset($this->_pk[$key])) {
			return $this->_pk[$key]['value'];
		}
		elseif (isset($this->_fks[$key])) {
			return isset($this->_fks[$key]['value']) ? $this->_fks[$key]['value'] : null;
		}
		elseif (isset($this->_fks[$key])) {
			return isset($this->_fks[$key]['value']) ? $this->_fks[$key]['value'] : null;
		}
		elseif (isset($this->_hasAndBelongsToMany[$key])) {
			return isset($this->_hasAndBelongsToMany[$key]['values']) ? $this->_hasAndBelongsToMany[$key]['values'] : null;
		}
		else {
			return null;
		}
	}

	public function __isset($key)
	{
		return $this->$key != NULL;
	}

	protected function _fetch($class, $where = 'true', $values = array(), $additionnals = '', $joins = array())
	{
		$query = $this->_getQuery($where, $values, $additionnals, $joins);

		$result = array();

		while($row = $query->fetch(PDO::FETCH_OBJ)){
			$obj = new $class();
			$obj->bind($row);
			$result[] = $obj;
		}

		return $result;
	}

	protected static function _fetchOne($class, $where = 'true', $values = array(), $additionnals = '', $joins = array(), $fields = array())
	{
		$obj = new $class;
		$result = $obj->_fetch($class, $where, $values, $additionnals . ' limit 1', $joins);
		return isset($result[0]) ? $result[0] : null;
	}

	/**
	 *
	 * $join : array(
	 *	  $table: array(
	 *		  'on': 'field1 = field2',
	 *		  'type': 'INNER',
	 *	  ),
	 *	  $table: array(
	 *		  'using': 'field',
	 *		  'type': 'INNER',
	 *	  ),
	 * )
	 *
	 */
	private function _getQuery($where = '', $values = array(), $additionnals = '', $joins = array())
	{
		$f = array();
		$fields = array_merge(array_keys($this->_pk), array_keys($this->_fields), array_keys($this->_fks));
		$nbFields = count($fields);
		for ($i = 0 ; $i < $nbFields ; $i ++) {
			$f[] = $this->_tableName . '.' . $fields[$i];
		}

		unset($fields);
		unset($nbFields);
		$f = implode(', ', $f);

		$j = array();
		foreach ($joins as $table => $join) {
			if (isset($join['on'])) {
				$j[] = (isset($join['type']) ? $join['type'] : 'INNER') . " JOIN $table ON {$join['on']}";
			}
			elseif (isset($join['using'])) {
				$j[] = (isset($join['type']) ? $join['type'] : 'INNER') . " JOIN $table USING ({$join['using']})";
			}
			if (count($j) > 0 && isset($join['fields'])) {
				$f .= ', ' . $join['fields'];
			}
		}

		if ($where != '') {
			$where = ' WHERE ' . $where;
		}

		$sql = 'SELECT '. $f .' FROM ' . $this->_tableName . ' ' . implode(' ', $j) . $where . ' ' . $additionnals;
		unset($f);

		$stmt = static::getDbAdapter()->prepare($sql);
		$stmt->execute($values);

		if ($stmt->errorCode() != '00000') {
			$errorInfo = $stmt->errorInfo();
			throw new Butterfly_Exception('Error : ' . $stmt->errorCode() . ' : ' . $errorInfo[2]);
		}

		return $stmt;
	}

	public function bind($values)
	{
		foreach($values as $key => $value){
			if (is_string($value)) {
				$this->$key = stripslashes($value);
			}
			else {
				$this->$key = $value;
			}
		}
	}

	public static function getDbAdapter()
	{
		if (empty(static::$_db)) {
			$db = Butterfly_Config_Ini::load(CONFIG_FILE, APPLICATION_ENV)->db;

			try {
				static::$_db = new PDO("{$db['sgbd']}:host={$db['host']};dbname={$db['base']}", $db['user'], $db['pass']);
			}
			catch (PDOException $e) {
				Throw new Butterfly_Exception($e->getMessage());
			}
		}

		return static::$_db;
	}

	/**
	 *
	 * load a row by his id
	 *
	 * @param int $id id of the role
	 *
	 */
	public static function loadById($class, $id, $where = '')
	{
		$object = new $class;
		if (is_array($id)) {
			$pk = array();
			foreach ($this->_pk as $key => $value) {
				$pk[] = $key . ' = :' . $key;
			}
			$where .= ($where == '' ? '' : ' AND ') . implode(' AND ', $pk);
			$params = $id;
		}
		else {
			$pk = array_keys($object->_pk);
			$where .= ($where == '' ? '' : ' AND ') . $pk[0] . ' = :id';
			$params = array('id' => $id);
		}
		$object = static::_fetchOne($class, $where, $params);

		return $object;
	}

	public function save()
	{
		static::getDbAdapter()->beginTransaction();

		$insert = false;
		foreach ($this->_pk as $value) {
			if (empty($value)) {
				$insert = true;
			}
		}

		if ($insert) {
			$return = $this->_insert();
		}
		else {
			$return = $this->_update();
		}

		foreach ($this->_hasAndBelongsToMany as $class => $infos) {
			$sqlDelete = '
				DELETE
					FROM ' . $infos['joinTable'] . '
					WHERE ' . $this->getPkName() . ' = :object'
			;

			$params = array('object' => $this->getPkValue());

			$stmt = static::getDbAdapter()->prepare($sqlDelete);
			$stmt->execute($params);

			$emptyObject = new $class;
			$sqlValues = array();
			$paramsInsert = array('object_id' => $this->getPkValue());
			$nbBelongs = isset($infos['values']) ? count($infos['values']) : 0;

			if ($return && $nbBelongs > 0) {
				for ($i = 0 ; $i < $nbBelongs ; $i++) {
					$sqlValues[] = '(:object_id, :belong' . $i . ', NOW(), NOW())';
					$paramsInsert['belong' . $i] = $infos['values'][$i];
				}

				$sqlInsert = '
					INSERT INTO ' . $infos['joinTable'] . '
						(' . $this->getPkName() . ', ' . $emptyObject->getPkName() . ', date_creation, date_update)
						VALUES ' . implode(', ', $sqlValues);

				$stmt = static::getDbAdapter()->prepare($sqlInsert);
				$stmt->execute($paramsInsert);

				if ($stmt->errorCode() != '00000') {
					$return = false;
				}
				else {
					$return = $return && true;
				}
			}

		}

		if ($return) {
			static::getDbAdapter()->commit();
		}
		else {
			static::getDbAdapter()->rollback();
		}

		return $return;
	}

	protected function _insert()
	{
		//INSERT INTO ma table () VALUES ()
		$fields = array();
		$values = array();

		foreach ($this->_fields as $key => $infos) {
			if ($key != 'date_creation' && $key != 'date_update' && isset($infos['value'])) {
				$fields[] = $key;
				$values[':' . $key] = $infos['value'];
			}
		}

		foreach ($this->_fks as $key => $infos) {
			if (isset($infos['value'])) {
				$fields[] = $key;
				$values[':' . $key] = $infos['value'];
			}
		}

		$values[':date_creation'] = date('Y-m-d H:i:s');
		$fields[] = 'date_creation';
		$values[':date_update'] = date('Y-m-d H:i:s');
		$fields[] = 'date_update';

		sort($fields);
		$valuesKeys = array_keys($values);
		sort($valuesKeys);

		$sql = 'INSERT INTO ' . $this->_tableName . '
		(' . implode(', ', $fields) . ')
		VALUES (' . implode(', ', $valuesKeys) . ')';

		$stmt = static::getDbAdapter()->prepare($sql);
		$stmt->execute($values);

		//@TODO the sequence must be added for pgsql...
		$this->{$this->getPkName()} = static::getDbAdapter()->lastInsertId();

		if ($stmt->errorCode() != '00000') {
			$errorInfo = $stmt->errorInfo();
			throw new Exception('Error : ' . $stmt->errorCode() . ' : ' . $errorInfo[2] . '<br />' . $sql);
		}

		return true;
	}

	protected function _update()
	{
		$fields = array();
		$values = array();
		foreach ($this->_fields as $key => $infos) {
			if ($key != 'date_creation' && $key != 'date_update' && isset($infos['value'])) {
				$fields[] = $key . ' = :' . $key;
				$values[':' . $key] = $infos['value'];
			}
		}

		foreach ($this->_fks as $key => $infos) {
			if (array_key_exists('value', $infos)) {
				$fields[] = $key . ' = :' . $key;
				$values[':' . $key] = $infos['value'];
			}
		}

		$values['date_update'] = date('Y-m-d H:i:s');
		$fields[] = 'date_update = ' . ':date_update';

		$pk = array();
		foreach ($this->_pk as $key => $value) {
			$pk[] = $key . ' = :' . $key;
			$values[':' . $key] = $value['value'];
		}

		$sql = 'UPDATE ' . $this->_tableName . '
		SET ' . implode(', ', $fields) . '
		WHERE ' . implode(' AND ', $pk);

		$stmt = static::getDbAdapter()->prepare($sql);
		$stmt->execute($values);

		if ($stmt->errorCode() != '00000') {
			$errorInfo = $stmt->errorInfo();
			throw new Exception('Error : ' . $stmt->errorCode() . ' : ' . $errorInfo[2] . '<br />' . $sql);
		}

		return true;
	}

	public function delete()
	{
		$pk = array();
		$values = array();
		foreach ($this->_pk as $key => $value) {
			$pk[] = $key . ' = :' . $key;
			$values[':' . $key] = $value['value'];
		}
		$sql = 'DELETE FROM ' . $this->_tableName . '
		WHERE ' . implode(' AND ', $pk);

		$stmt = static::getDbAdapter()->prepare($sql);
		$stmt->execute($values);

		if ($stmt->errorCode() != '00000') {
			$errorInfo = $stmt->errorInfo();
			throw new Exception('Error : ' . $stmt->errorCode() . ' : ' . $errorInfo[2]);
		}
	}

	public function loadHasAndBelongToMany()
	{
		foreach ($this->_hasAndBelongsToMany as $class => $values) {
			$emptyObject = new $class;

			//load Objects
			$this->_hasAndBelongsToMany[$class]['values'] = $emptyObject->_fetch(
				$class,
				$values['joinTable'] . '.' . $this->getPkName() . '=:key',
				array('key' => $this->getPkValue()),
				'',
				array(
					$values['joinTable'] => array(
						'on' => $values['joinTable'] . '.' . $emptyObject->getPkName() . '=' .
							$emptyObject->getTableName() . '.' . $emptyObject->getPkName()
					)
				)
			);
		}

	}

	public function getForm(array $errors = array())
	{
		$form = new Butterfly_Form();
		$fields = array_merge($this->_fields);
		$table = new Butterfly_Form_Element_Decorator_Table();

		foreach ($fields as $name => $infos) {
			if (isset($infos['type'])) {
				$type = $infos['type'];
				$element = new Butterfly_Form_Element_Decorator_Label(
					Butterfly_Form_Element_Factory::get(
						$type,
						$name,
						$this->$name
					),
					$name,
					$infos['desc'],
					isset($errors[$name]) ? '<span class="error">' . $errors[$name] . '</span>' : ''
				);
				$table->addElement($element);
			}
		}

		foreach ($this->_fks as $name => $infos) {
			if (isset($infos['desc'])) {
				$elts = $infos['class']::loadAllActives();
				$nbElements = count($elts);

				$elements = array('' => 'aucun');
				for ($i = 0 ; $i < $nbElements ; $i ++) {
					$elements[$elts[$i]->{$elts[$i]->getPkName()}] = $elts[$i]->{$elts[$i]->getTableName() . '_label'};
				}

				$element = new Butterfly_Form_Element_Decorator_Label(
					Butterfly_Form_Element_Factory::get(
						Butterfly_Form_Element_Factory::TYPE_LIST,
						$name,
						$this->$name,
						array(
							'values' => $elements
						)
					),
					$name,
					$infos['desc'],
					isset($errors[$name]) ? '<span class="error">' . $errors[$name] . '</span>' : ''
				);
				$table->addElement($element);
			}
		}

		foreach ($this->_hasAndBelongsToMany as $class => $infos) {
			if (isset($infos['desc'])) {
				$elts = $class::loadAllActives();
				$nbElements = count($elts);

				$elements = array();
				//foreach items
				for ($i = 0 ; $i < $nbElements ; $i ++) {
					//Create a label which contains a checkbox
					$element = new Butterfly_Form_Element_Decorator_Label(
						new Butterfly_Form_Element_CheckBox(
							$class . '[]',
							strtolower($class) . '_' . $elts[$i]->getPkValue(),
							$elts[$i]->getPkValue(),
							(!empty($infos['values']) && in_array($elts[$i], $infos['values']) ? array('checked' => 'checked') : array())
						),
						strtolower($class) . '_' . $elts[$i]->getPkValue(),
						$elts[$i]->{$elts[$i]->getTableName() . '_label'},
						isset($errors[strtolower($class) . '_' . $elts[$i]->getPkValue()]) ? '<span class="error">' . $errors[strtolower($class) . '_' . $elts[$i]->getPkValue()] . '</span>' : ''
					);
					$elements[] = $element;
				}

				//create a multiplecheckbox set
				$elements = new Butterfly_Form_Element_CheckBox_Multiple($elements);

				//create a label to the set
				$element = new Butterfly_Form_Element_Decorator_Label(
					$elements,
					strtolower($class),
					$infos['desc'],
					''
				);

				//all all in the form
				$table->addElement($element);
			}
		}

		$table->addElement(new Butterfly_Form_Element_Submit('submit_db_form', 'submit_db_form', 'Submit'));
		$form->addElement($table);

		unset($fields);
		unset($table);
		unset($element);
		unset($type);

		return $form;
	}

	public function getPkName()
	{
		if (count($this->_pk) == 1) {
			$pks = array_keys($this->_pk);
			return $pks[0];
		}
		else {
			return array_keys($this->_pk);
		}
	}

	public function getPkValue()
	{
		if (count($this->_pk) == 1) {
			$pks = array_values($this->_pk);
			return $pks[0]['value'];
		}
		else {
			return array_values($this->_pk);
		}
	}

	public function getTableName()
	{
		return $this->_tableName;
	}
}
