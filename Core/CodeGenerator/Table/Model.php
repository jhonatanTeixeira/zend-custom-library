<?php
class Core_CodeGenerator_Table_Model extends Core_CodeGenerator_Table_Abstract
{
    protected function _setupClassPrefix()
    {
        $this->_classPreffix = "Model_";
    }

    protected function _setupExtendFrom()
    {
        $this->_extendFrom = "Core_Db_Row";
    }
}