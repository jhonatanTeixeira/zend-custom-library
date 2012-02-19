<?php
class Core_Db_Row_List extends Zend_Db_Table_Row_Abstract
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);

        foreach ($this->_data as $field => $data) {
            if ($field != 'id' and $field != 'name') {
                unset ($this->_data[$field]);
            }
        }
    }
}