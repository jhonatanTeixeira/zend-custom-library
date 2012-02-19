<?php
class Core_Form_Default extends Core_Form
{
    /**
     *
     * @var Core_Form
     */
    private $_modelForm;

    public function __construct($modelForm)
    {
        $this->_modelForm = (string) $modelForm;
        $this->create();
    }

    public function create()
    {
        $reflection = new ReflectionClass($this->_modelForm);

        $this->_create();

        $instance = $reflection->newInstance();

        foreach ($reflection->getMethods(ReflectionMethod::IS_PROTECTED) as $method) {
            if ($method->name == '_create' 
                or $method->name == '_addElement'
                or $method->name == '_addElementsFromArray'
                or $method->name == '_addSubmit') {
                continue;
            }
            
            $method->setAccessible(true);
            $element = $method->invoke($instance);
            if ($element instanceof Zend_Form_Element) {
                $this->_addElement($element);
            }
        }

        $this->_addSubmit();
    }
}