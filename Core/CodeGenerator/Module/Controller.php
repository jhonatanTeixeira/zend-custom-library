<?php

class Core_CodeGenerator_Module_Controller extends Core_CodeGenerator_Module_Abstract
{
    protected function _setupClassPrefix()
    {
        $this->_classPreffix = ucfirst($this->_moduleName) . "_";
    }

    protected function _setupExtendFrom()
    {
        $this->_extendFrom = "Core_Controller_AdminCrud";
    }

    protected function _setupType()
    {
        $this->_type = "controllers";
    }

    protected function _getClassName()
    {
        return $this->_table->getName() . "Controller";
    }

}