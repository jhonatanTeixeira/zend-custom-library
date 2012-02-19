<?php
class Core_Filter_TableNameToDbTable extends Core_Filter_TableNameToModel
{
    public function filter($value)
    {
        $this->_filter->setTarget("Model_DbTable_:tablename");

        return $this->_filter->filter(array('tablename' => $value));
    }
}