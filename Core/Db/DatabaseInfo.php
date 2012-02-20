<?php
class Core_Db_DatabaseInfo implements IteratorAggregate
{
    private $_data = array();
    private $_dbName;

    public function __construct()
    {
        $this->_adapter = Zend_Db_Table::getDefaultAdapter();
        $config = $this->_adapter->getConfig();
        $this->_dbName = $config['dbname'];
    }


    public function __get($name)
    {
        $inflector = new Zend_Filter();
        $inflector->addFilter(new Zend_Filter_Word_CamelCaseToUnderscore());
        $name = $inflector->filter($name);

        if (!isset($this->_data[$name])) {
            $this->_data[$name] = new Core_Db_DatabaseInfo_Table($name);
        }

        return $this->_data[$name];
    }

    public function loadAllTables()
    {
        $tables = $this->_executeQuery();

        foreach ($tables as $table) {
            $indexName = "Tables_in_{$this->_dbName}";
            $tableName = $table[$indexName];
            $this->_data[$tableName] = new Core_Db_DatabaseInfo_Table($tableName);
        }
    }

    private function _executeQuery()
    {
        $stm = $this->_adapter->query("show tables");
        $stm->execute();

        return $stm->fetchAll();
    }

    public function getIterator()
    {
        if (!count($this->_data) > 0) {
            $this->loadAllTables();
        }
        
        return new ArrayIterator($this->_data);
    }
}