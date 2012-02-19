<?php
class Core_Db_DatabaseInfo_Field
{
    private $_data = array();

    public function __construct(array $fieldInfo)
    {
        $this->_data = $fieldInfo;
    }

    public function __get($name)
    {
        $inflector = new Zend_Filter();
        $inflector->addFilter(new Zend_Filter_Word_CamelCaseToUnderscore())
            ->addFilter(new Zend_Filter_StringToUpper());
        $name = $inflector->filter($name);

        return $this->_data[$name];
    }

    public function __call($name, $arguments)
    {
        $name = str_replace("get", "", $name);

        return $this->__get($name);
    }

    public function isPrimary()
    {
        return $this->_data['PRIMARY'];
    }

    public function isIdentity()
    {
        return $this->_data['IDENTITY'];
    }

    public function isNullable()
    {
        return $this->_data['NULLABLE'];
    }

    public function __toString()
    {
        return $this->_data['COLUMN_NAME'];
    }
}