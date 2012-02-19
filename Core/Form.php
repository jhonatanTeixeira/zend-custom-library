<?php
abstract class Core_Form
{
	protected $_form;
	
	function __construct($formName = null)
	{
		if ($formName) {
			$formName = "_" . $formName;
            $this->_create();
            $this->_addElementsFromArray($this->$formName);
            $this->_addSubmit();
		}
	}

    public function __call($name, $args)
    {
        return call_user_func_array(array($this->_form, $name), (array) $args);
    }
	
	function __toString()
	{
		return (string) $this->_form;
	}
	
	protected function _create()
	{
		$this->_form = new Zend_Dojo_Form();
        $this->_form->setDecorators(array('FormElements', 'DijitForm'));
        $this->_form->setMethod('post');
	}

    protected function _addElement(Zend_Form_Element $element)
    {
        $this->_form->addElement($element);
    }

    protected function _addElementsFromArray(array $fields)
    {
        foreach ($fields as $field) {
            $methodName = "_create" . ucfirst($field) . "Element";
            $this->_addElement($this->$methodName());
        }
    }

    protected function _addSubmit()
    {
        $this->_form->addElement(new Zend_Dojo_Form_Element_SubmitButton('Send'));
    }

    /**
     *
     * @return Zend_Form
     */
    public function getForm()
    {
        return $this->_form;
    }
	
}