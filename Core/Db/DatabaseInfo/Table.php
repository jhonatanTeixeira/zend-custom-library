<?php
class Core_Db_DatabaseInfo_Table implements IteratorAggregate
{
    /**
     *
     * @var Zend_Db_Adapter_Abstract
     */
    private $_adapter;

    private $_data = array();

    private $_name;

    public function __construct($name)
    {
        $this->_adapter = Zend_Db_Table::getDefaultAdapter();
        $this->_loadTable($name);
        $this->_name = $name;
    }

    private function _loadTable($tableName)
    {
        $fields = $this->_adapter->describeTable($tableName);
        foreach ($fields as $field => $attr) {
            $this->_data[$field] = new Core_Db_DatabaseInfo_Field($attr);
        }
    }

    public function __get($name)
    {
        return $this->_data[$name];
    }

    public function getDbTableName()
    {
        $inflector = new Core_Filter_TableNameToDbTable();
        return $inflector->filter($this->_name);
    }

    public function getModelName()
    {
        $inflector = new Core_Filter_TableNameToModel();
        return $inflector->filter($this->_name);
    }

    /**
     * gets the name of the table
     *
     * @return <type> 
     */
    public function getName()
    {
        return $this->_name;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->_data);
    }

    /**
     *
     * @return Core_Db_DatabaseInfo_Field
     */
    public function getIdentityField()
    {
        foreach ($this as $field) {
            if ($field->isIdentity()) {
                return $field;
            }
        }

        return null;
    }

    public function hasField($name)
    {
        return isset($this->_data[$name]);
    }
}