<?php

class Core_CodeGenerator_Module_Form extends Core_CodeGenerator_Module_Abstract
{
    protected function _setupClassPrefix()
    {
        $this->_classPreffix = ucfirst($this->_moduleName) . "_Form_";
    }

    protected function _setupExtendFrom()
    {
        $this->_extendFrom = $this->_table->getFormName();
    }

    protected function _setupType()
    {
        $this->_type = "forms";
    }
}