<?php

class Butterfly_Acl_User extends Butterfly_Db_Abstract
{
    protected $_tableName = 'acl_user';

    //use constants for types ?
    protected $_fields = array(
        'acl_user_login' => array('type' => Butterfly_Form_Element_Factory::TYPE_TEXT, 'desc' => 'Login'),
        'acl_user_passwd' => array('type' => Butterfly_Form_Element_Factory::TYPE_PASSWORD, 'desc' => 'Password'),
        'id_acl_role' => array(),
        'acl_user_actif' => array(),
        'ip_last_connexion' => array(),
        'date_last_connexion' => array(),
        'date_creation' => array(),
        'date_update' => array(),
    );

    protected $_pk = array('id_acl_user' => null);

    //@TODO : change to dynamic management of foreign keys
    private $_role;

    protected static function _getClass()
    {
        return __CLASS__;
    }

    public static function getConnectedUser()
    {
        $acl = Butterfly_Session::get('acl');

        //if a user is connected
        if ($acl != null && $acl['hash'] != null) {
            $user = self::loadByLogin($acl['acl_user_login']);
            //the connected used is correct
            if ($user != null && $acl['hash'] == sha1($user->acl_user_passwd)) {
                Butterfly_Session::regenerate();
                return $user;
            }
            else {
                Butterfly_Session::delete('acl');
                return null;
            }
        }
        else {
            Butterfly_Session::delete('acl');
            return null;
        }
    }

    public static function loadByLogin($login)
    {
        return self::_fetchOne('Butterfly_Acl_User', 'acl_user_login = :login', array(':login' => $login));
    }

    public static function loadByLoginAndPassword($login, $passwd)
    {
        return self::_fetchOne('Butterfly_Acl_User',
                                'acl_user_login = :login AND acl_user_passwd = :passwd',
                                array(':login' => $login, 'passwd' => sha1($passwd)));
    }

    public function connect()
    {
        $acl = array();
        $acl['user'] = $this;
        $this->date_last_connexion = date('Y-m-d H:i:s');
        $this->ip_last_connexion = $_SERVER['REMOTE_ADDR'];
        $this->save();
        $acl['hash'] = sha1($this->acl_user_passwd);
        Butterfly_Session::set(
            'acl',
            array(
                'id_acl_user' => $this->id_acl_user,
                'acl_user_login' => $this->acl_user_login,
                'acl_user_passwd' => $this->acl_user_passwd,
                'hash' => sha1($this->acl_user_passwd)
            ),
            true
        );
    }

    public function hasAccessToResource($idResource)
    {
        $stmt = $this->getAdapter()->prepare('SELECT id_acl_resource FROM acl_resource_user WHERE id_acl_user = :user');
        $row = $stmt->execute(array('user' => $this->id_acl_user));

        return $stmt->fetch() != false;
    }

    public function hasAccessToPage($module, $action, $idSite)
    {
        $resource = Butterfly_Acl_Resource::loadFromRequest($module, $action, $idSite);

        if (!$resource) {
            return true;
        }

        return $this->hasAccessToResource($resource->id_acl_resource) || $this->getRole()->hasAccessToResource($resource->id_acl_resource);
    }

    public function getRole()
    {
        if ($this->_role == null) {
            $this->_role = Butterfly_Acl_Role::loadById('Butterfly_Acl_Role', $this->id_acl_role);
        }

        return $this->_role;
    }

    public function deconnect()
    {
        Butterfly_Session::delete('acl');
    }

    /**
     *
     * Load all the acl users
     *
     */
    public static function loadAll($where = '', $values = array(), $additionnals = '', $join = array())
    {
        $user = new self;
        return $user->_fetch('Butterfly_Acl_User', $where, $values, $additionnals, $join);
    }
}
