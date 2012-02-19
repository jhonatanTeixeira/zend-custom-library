<?php

class App_Acl_Assertion implements Zend_Acl_Assert_Interface
{

    /**
     *
     * @var Core_Db_Rowset
     */
    private $_assertions;

    public function setAssertions(Core_Db_Rowset $assertions)
    {
        $this->_assertions = $assertions;
    }

    public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null,
        Zend_Acl_Resource_Interface $resource = null, $privilege = null)
    {
        $currentStatus = $acl->getCurrentStatusId();

        if (!$currentStatus) {
            return true;
        }
        
        $assertion = $this->_assertions->getRowByField(
            'idStatus',
            $currentStatus
        );
        
        if (!is_null($assertion)) {
            return true;
        }

        return false;
    }
}