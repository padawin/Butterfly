<?php

class Butterfly_Factory
{
	protected static $_namespaces = array();

	public static function setNamespaces($namespaces = array())
	{
		if (is_string($namespaces)) {
			self::$_namespaces = array($namespaces);
		}
		elseif (is_array($namespaces)) {
			self::$_namespaces = $namespaces;
		}
		else {
			throw new Exception('Invalid namespaces type');
		}
	}

	public static function create($className)
	{
		return self::_loadClass($className, true);
	}

	public static function getClass($className)
	{
		return self::_loadClass($className, false);
	}

	protected function _loadClass($className, $instantiate)
	{
		try {
			if (class_exists($className)) {
				return $instantiate ? new $className : $className;
			}
		}
		catch (Exception $e) {
			foreach (self::$_namespaces as $namespace) {
				try {
					$class = "{$namespace}_{$className}";
					if (class_exists($class)) {
						return $instantiate ? new $class : $class;
					}
				}
				catch (Exception $e)
				{
					continue;
				}
			}
		}

		throw new Exception("Unknown class {$className}");
	}

	public static function cleanNamespace($className)
	{
		return substr($className, strpos($className, '_') + 1);
	}
}
