<?php
class App_Controller_Plugin_Login extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $auth = Zend_Auth::getInstance();

        $allowed = new Zend_Config_Xml(
            APPLICATION_PATH . '/configs/noLogin.xml',
            APPLICATION_ENV
        );

        foreach ($allowed as $allow) {
            foreach ($allow as $module => $controller) {
                if ($request->getModuleName() == $module) {
                    if ($controller == 'all') {
                        return;
                    }

                    $actions = trim($controller->get($request->getControllerName()));

                    if ($actions == "all") {
                        return true;
                    }

                    foreach ((array) $actions->action as $action) {
                        if ($action == $request->getActionName()) {
                            return;
                        }
                    }
                }
            }
        }

        if (!$auth->hasIdentity()) {
            $request->setControllerName('funcionario')
                ->setActionName('login');
        }
    }
}