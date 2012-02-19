<?php
class Core_View_Helper_Anchor extends Zend_View_Helper_Abstract
{
    public function anchor($content, array $options, array $args = array(), $class = null)
    {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            return false;
        }
        
        $front = Zend_Controller_Front::getInstance();
        $user = Zend_Auth::getInstance()->getIdentity();
        
        if (!isset($options['controller'])) {
            $options['controller'] = $front->getRequest()->getControllerName();
        }

        if (!isset($options['module'])) {
            $options['module'] = $front->getRequest()->getModuleName();
        }

        if (!isset($options['action'])) {
            throw new Exception('action is obrigatory');
        }

        $resource = "mvc:{$options['module']}";

        if (!App_Acl::getInstance()->isAllowed($user->idUser, $resource)) {
            return false;
        }

        $link = $this->view->url(
            $options + $args,
            null,
            true
        );

        return "<a href='$link' class='$class'>$content</a>";
    }
}