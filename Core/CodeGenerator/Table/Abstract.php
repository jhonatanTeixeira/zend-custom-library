<?php
abstract class Core_CodeGenerator_Table_Abstract extends Core_CodeGenerator_Abstract_Class
{
    /**
     * the relationships to be used on the methods and properties
     *
     * @var array
     */
    protected $_relationShips = array();

    /**
     * the table to be used
     *
     * @var Core_Db_DatabaseInfo_Table
     */
    protected $_table;

    /**
     * sets the table to be used on the generation
     *
     * @param Core_Db_DatabaseInfo_Table $table
     * @return Core_CodeGenerator_Table_Abstract 
     */
    public function setTable(Core_Db_DatabaseInfo_Table $table)
    {
        $this->_table = $table;

        return $this;
    }

    /**
     * set the table relationships
     *
     * @param array $relationShips 
     */
    public function setRelationShips(array $relationShips)
    {
        $this->_relationShips = $relationShips;
    }

    protected function _getClassName()
    {
        return $this->_table->getName();
    }
}