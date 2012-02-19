<?php
abstract class Core_Controller_Action extends Zend_Controller_Action
{
    /**
     *
     * @var Core_Controller_CrudObject
     */
    protected $_crudObject;

    public function __construct(Zend_Controller_Request_Abstract $request,
        Zend_Controller_Response_Abstract $response,
        array $invokeArgs = array())
    {
        $this->_crudObject = new Core_Controller_CrudObject();
        $name = preg_replace('/^[a-zA-Z]+_|Controller$/', '', get_class($this));
        $front = Zend_Controller_Front::getInstance();
        $this->_crudObject->setControllerName(
            $request->getControllerName()
        );
        $this->_crudObject->setModelClassName(
            "Model_" . ucfirst($request->getControllerName())
        );
        $this->_crudObject->setDbTableClassName(
            "Model_DbTable_" . ucfirst($request->getControllerName())
        );
        $this->_crudObject->setFormClassName(
            ucfirst($request->getModuleName()) . '_Form_' . ucfirst($request->getControllerName())
        );
        $this->_crudObject->setCrudName($request->getControllerName());

        parent::__construct($request, $response, $invokeArgs);
    }

	function isPost()
	{
		return $this->_request->isPost();
	}
	
	function getPost($asObject = false)
	{
		return ($asObject)
			? (object) $this->_request->getPost()
			: $this->_request->getPost();
	}
	
	function __get($attr)
	{
		$attr = ucfirst(strtolower($attr));

        if (!Zend_Registry::isRegistered($attr)) {
            $className = "Model_DbTable_$attr";
            Zend_Registry::set($attr, new $className());
		}
		
		return Zend_Registry::get($attr);
	}

    public function render($action = null, $name = null, $noController = false)
    {
        try {
            parent::render($action, $name, $noController);
        } catch (Exception $e) {
            
        }
    }

}