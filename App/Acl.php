<?php
class App_Acl extends Zend_Acl
{
    private static $_instance;

    private static $_session;

    /**
     *
     * @var int
     */
    private $_statusId;

    /**
     *
     * @var Core_Db_Row
     */
    private $_user;

    /**
     *
     * @var Core_Db_Rowset
     */
    private $_modules;

    private function  __construct()
    {
        
    }

    private function __clone()
    {
        
    }

    /**
     *
     * @return App_Acl
     */
    public static function getInstance()
    {
        if (!isset(self::$_session)) {
            self::$_session = new Zend_Session_Namespace(__CLASS__);
        }

        if (!self::$_instance instanceof  self) {
            if (self::$_session->acl instanceof  self) {
                self::$_instance = self::$_session->acl;
            } else {
                self::$_instance = new self();
            }
        }

        return self::$_instance;
    }

    public function getModules()
    {
        return $this->_modules;
    }

    public function setUser($user)
    {
        $this->_user = $user;

        return $this;
    }

    public function setCurrentStatusId($idStatus)
    {
        $this->_statusId = (int) $idStatus;
    }

    public function getCurrentStatusId()
    {
        return $this->_statusId;
    }

    public function start()
    {
        $this->_modules = Core_Db_Table::loadTable('module');

        foreach ($this->_modules as $module) {
            foreach ($module->resource as $resource) {
                $resourceName = "mvc:{$module->nameModule}.{$resource->nameResource}";
                $this->addResource($resourceName);

                foreach ($resource->privilege as $privilege) {
                    foreach ($privilege->permission as $permission) {
                        if ($permission->idUser == $this->_user->idUser) {
                            $assert = new App_Acl_Assertion();
                            $assert->setAssertions($permission->assertion);

                            $this->allow(
                                $permission->idUser,
                                $resourceName,
                                $privilege->namePrivilege,
                                $assert
                            );
                        }
                    }
                }
            }
        }

        self::$_session->acl = $this;
    }

    public function isAllowed($role = null, $resource = null, $privilege = null)
    {
        try {
            return parent::isAllowed($role, $resource, $privilege);
        } catch (Exception $exception) {
            return true;
        }
    }
}