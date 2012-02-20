<?php
class Core_Filter_TableNameToForm extends Core_Filter_TableNameToModel
{
    public function filter($value)
    {
        $this->_filter->setTarget("Model_Form_:tablename");

        return $this->_filter->filter(array('tablename' => $value));
    }
}