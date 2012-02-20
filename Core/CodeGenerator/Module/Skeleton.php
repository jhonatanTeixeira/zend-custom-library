<?php
class Core_CodeGenerator_Module_Skeleton
{
    /**
     *
     * @var array of string
     */
    protected $_tableList;

    protected $_moduleName;

    public function setTableList(array $list)
    {
        $this->_tableList = $list;
        return $this;
    }

    /**
     *
     * @param string $name
     * @return Core_CodeGenerator_Module_Skeleton
     */
    public function setModuleName($name)
    {
        $this->_moduleName = (string) $name;
        return $this;
    }

    public function generate()
    {
        if (!isset($this->_moduleName)) {
            throw new Exception("no module name set");
        }

        if (!isset($this->_tableList)) {
            throw new Exception("no table list set");
        }

        $path = APPLICATION_PATH . "/modules/{$this->_moduleName}";

        if (!file_exists($path . "/controllers")) {
            mkdir($path . "/controllers", 777, true);
        }

        if (!file_exists($path . "/forms")) {
            mkdir($path . "/forms", 777, true);
        }
        
        foreach ($this->_tableList as $table) {
            $tableInfo = Core_Db_DatabaseInfo_Table::get($table);
            $controllerGen = new Core_CodeGenerator_Module_Controller();
            $controllerGen->setModuleName($this->_moduleName)
                ->setTable($tableInfo)
                ->generate();

            $formGen = new Core_CodeGenerator_Module_Form();
            $formGen->setModuleName($this->_moduleName)
                ->setTable($tableInfo)
                ->generate();

            mkdir($path . "/views/scripts/{$table->getName()})", true);
        }

        
    }
}