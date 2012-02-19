<?php
class Core_Db_ModelGenerator
{
    /**
     *
     * @var Core_Db_DatabaseInfo
     */
    private $_databaseInfo;

    private $_relationShips = array();

    public function __construct()
    {
        $this->_databaseInfo = new Core_Db_DatabaseInfo();
        $this->_databaseInfo->loadAllTables();
    }

    private function _detectRelationships()
    {
        foreach ($this->_databaseInfo as $table) {
            $identity = (string) $table->getIdentityField();
            foreach ($this->_databaseInfo as $subTable) {
                if ($subTable->getName() != $table->getName()
                    and $subTable->hasField($identity)) {
                    $this->_relationShips[$subTable->getName()][] = array(
                        'parentTable' => $table,
                        'table' => $subTable
                    );
                }
            }
        }
    }

    public function createModels()
    {
        $this->_detectRelationships();

        foreach ($this->_databaseInfo as $table) {
            $model   = new Core_CodeGenerator_Table_Model();
            $dbTable = new Core_CodeGenerator_Table_DbTable();

            $model->setTable($table);
            $dbTable->setTable($table);

            if (isset($this->_relationShips[$table->getName()])) {
                $dbTable->setRelationShips($this->_relationShips[$table->getName()]);
            }

            $model->generate();
            $dbTable->generate();
        }
    }

    public function createForms()
    {
        $this->_detectRelationships();
        
        foreach ($this->_databaseInfo as $table) {
            $form = new Core_CodeGenerator_Table_Form();
            $form->setTable($table);

            if (isset($this->_relationShips[$table->getName()])) {
                $form->setRelationShips($this->_relationShips[$table->getName()]);
            }

            $form->generate();
        }
    }
}
