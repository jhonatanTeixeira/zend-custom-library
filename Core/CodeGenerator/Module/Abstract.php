<?php
abstract class Core_CodeGenerator_Module_Abstract extends Core_CodeGenerator_Abstract_Class
{
    /**
     * the table to be used
     *
     * @var Core_Db_DatabaseInfo_Table
     */
    protected $_table;

    /**
     * the module name being generated
     *
     * @var string
     */
    protected $_moduleName;

    /**
     * the class type: form, controller or view
     *
     * @var string
     */
    protected $_type;

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
     * sets the module name
     *
     * @param string $name
     * @return Core_CodeGenerator_Module_Abstract
     */
    public function setModuleName($name)
    {
        $this->_moduleName = $name;
        return $this;
    }

    /**
     * gets the path of the file to be generated
     *
     * @return string
     */
    protected function _getPath()
    {
        $fileName = $this->_inflector->setTarget(
            APPLICATION_PATH . '/:module:path:file.:suffix'
        )->filter(
            array(
                'module' => $this->_moduleName . "_",
                'path' => $this->_type . "_",
                'file' => $this->_getClassName()
            )
        );

        return $fileName;
    }

    protected function _getClassName()
    {
        return $this->_table->getName();
    }

    abstract protected function _setupType();
}