<?php
class App_Controller_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            return;
        }

        $module     = $request->getModuleName();
        $controller = $request->getControllerName();
        $action     = $request->getActionName();

        $resource = "mvc:$module.$controller";
        $identity = Zend_Auth::getInstance()->getIdentity()->idUser;

        if (!App_Acl::getInstance()->isAllowed($identity, $resource, $action)) {
            $messenger = $this->_helper->getHelper('FlashMessenger');
            foreach ($authResult->getMessages() as $message) {
                $messenger->addMessage(
                    "access to $resource/$action denied"
                );
            }

            $request->setControllerName('funcionario')
                ->setActionName('login');
        }
    }
}