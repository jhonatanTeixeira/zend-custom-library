<?php
class App_Controller_Plugin_NoRender extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch()
    {
        Zend_Controller_Action_HelperBroker::getExistingHelper('ViewRenderer')
            ->setNeverRender();

        Zend_Controller_Action_HelperBroker::getExistingHelper('Layout')
            ->disableLayout();
    }
}