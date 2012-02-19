<?php
class Core_Controller_CrudObject
{
    private $_controllerName;

    /**
     *
     * @var Core_Db_Row
     */
    private $_modelClass;

    /**
     *
     * @var Core_Db_Table
     */
    private $_dbTableClass;

    private $_modelClassName;

    private $_dbTableClassName;

    private $_formClassName;

    private $_linkPart;

    private $_crudName;

    private $_listFields = array('*');

    /**
     *
     * @return Core_Db_Row
     */
    public function getModelClass()
    {
        if (!$this->_modelClass instanceof Core_Db_Row) {
            $this->_modelClass = new $this->_modelClassName();
        }

        return $this->_modelClass;
    }

    /**
     *
     * @return Core_Db_Table
     */
    public function getDbTableClass()
    {
        if (!$this->_dbTableClass instanceof Core_Db_Table) {
            $this->_dbTableClass = new $this->_dbTableClassName();
        }

        return $this->_dbTableClass;
    }

    /**
     *
     * @return string
     */
    public function getControllerName()
    {
        return $this->_controllerName;
    }

    /**
     *
     * @return Core_Form
     */
    public function getFormClass($formName)
    {
        if (class_exists($this->_formClassName, true)) {
            return new $this->_formClassName($formName);
        }

        $modelFormName = "Model_Form_" . array_pop(
            explode("_", $this->_formClassName)
        );

        return new Core_Form_Default($modelFormName);
    }

    public function getLinkPart()
    {
        return $this->_linkPart;
    }

    public function setModelClassName($name)
    {
        $this->_modelClassName = (string) $name;
    }

    public function setDbTableClassName($name)
    {
        $this->_dbTableClassName = (string) $name;
    }

    public function setControllerName($name)
    {
        $this->_controllerName = (string) $name;
    }

    public function setFormClassName($name)
    {
        $this->_formClassName = (string) $name;
    }

    public function setLinkPart($link)
    {
        $this->_linkPart = (string) $link;
    }

    public function setCrudName($name)
    {
        $this->_crudName = (string) $name;
    }

    public function getCrudName()
    {
        return $this->_crudName;
    }

    public function getPrimaryField()
    {
        return $this->getDbTableClass()->getPrimary();
    }
}