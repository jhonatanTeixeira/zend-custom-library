<?php
class Core_Filter_TableNameToModel implements Zend_Filter_Interface
{
    /**
     *
     * @var Zend_Filter_Inflector
     */
    protected $_filter;

    public function __construct()
    {
        $this->_filter = new Zend_Filter_Inflector();
        $this->_filter->addFilterPrefixPath("Core_Filter", "Core/Filter")
            ->setRules(
                array(
                    ':tablename' => array('Ucfirst', 'Word_UnderscoreToCamelCase')
                )
            );
    }

    public function filter($value)
    {
        $this->_filter->setTarget("Model_:tablename");

        return $this->_filter->filter(array('tablename' => $value));
    }
}