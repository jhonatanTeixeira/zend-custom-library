<?php
class Core_Db_Select extends Zend_Db_Table_Select
{
    private $_isUsingJoins = false;

    private $_joinedModels = array();

    public function useJoins(array $joins)
    {
        $table = $this->getTable();

        foreach ($joins as $join) {
            $tableName = "Model_DbTable_" . ucfirst($join);
            $reference = $table->getReference($tableName);
            $this->joinLeftUsing(strtolower($join), $reference['columns'][0]);
        }

        $this->_isUsingJoins = true;
        $this->_joinedModels = $joins;
    }

    public function isUsingJoins()
    {
        return $this->_isUsingJoins;
    }

    public function getJoinedModels()
    {
        return $this->_joinedModels;
    }
}