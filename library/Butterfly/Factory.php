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
        foreach (self::$_namespace as $namespace) {
            try {
                $class = "{$namespace}_{$className}";
                $object = new $class;
                return $object;
            }
            catch (Exception $e)
            {
                continue;
            }

            return null;
        }
    }

    public static function cleanNamespace($className)
    {
        return substr($className, strpos($className, '_') + 1);
    }
}
