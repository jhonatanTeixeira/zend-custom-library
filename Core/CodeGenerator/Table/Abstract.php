<?php
abstract class Core_CodeGenerator_Table_Abstract
{
    /**
     *
     * @var Zend_Filter_Inflector
     */
    protected $_inflector;

    protected $_relationShips = array();

    /**
     *
     * @var Core_Db_DatabaseInfo_Table
     */
    protected $_table;

    protected $_classPreffix;

    protected $_extendFrom;

    public function __construct()
    {
        $this->_inflector = new Zend_Filter_Inflector();
        $this->_inflector->addFilterPrefixPath('Core_Filter', 'Core/Filter')
            ->setRules(
                array(
                    ':path'  =>  array('UnderscoreToDirSeparator'),
                    ':file'  =>  array('Ucfirst', 'Word_UnderscoreToCamelCase'),
                    'suffix' => 'php'
                )
            );

        $this->_setupClassPrefix();
        $this->_setupExtendFrom();
    }

    public function setTable(Core_Db_DatabaseInfo_Table $table)
    {
        $this->_table = $table;

        return $this;
    }

    /**
     * set the table relationships relationships
     *
     * @param array $relationShips 
     */
    public function setRelationShips(array $relationShips)
    {
        $this->_relationShips = $relationShips;
    }

    abstract protected function _setupClassPrefix();

    abstract protected function _setupExtendFrom();

    protected function _createClass()
    {
        $class = new Zend_CodeGenerator_Php_Class();
        
        $className = $this->_inflector->setTarget($this->_classPreffix . ':file')
            ->filter(
                array(
                    'file' => $this->_table->getName()
                )
            );

        $class->setExtendedClass($this->_extendFrom)
            ->setName($className)
            ->setDocblock("generated on " . Zend_Date::now());

        return $class;
    }

    protected function _createFile()
    {
        $file = new Zend_CodeGenerator_Php_File();

        $fileName = $this->_inflector->setTarget(
            APPLICATION_PATH . '/:path:file.:suffix'
        )
            ->filter(
                array(
                    'path' => $this->_classPreffix,
                    'file' => $this->_table->getName()
                )
            );
        $file->setFilename($fileName);

        return $file;
    }

    protected function _createAttributes()
    {
        //extends on childs classes who needs it
        return array();
    }

    /**
     * to be used on child classes who needs to implement methods
     *
     * @return array must return an array of Zend_CodeGenerator_Php_Method
     */
    protected function _createMethods()
    {
        //extends on childs classes who needs it
        return array();
    }

    public function generate()
    {
        $class = $this->_createClass()
            ->setProperties($this->_createAttributes());
        $file = $this->_createFile();

        $methods = $this->_createMethods();

        $class->setMethods($methods);

        $file->setClass($class);
        $file->write();
    }
}