<?php
class Core_View_Helper_FlashMessenger extends Zend_View_Helper_Abstract
{
    public function flashMessenger()
    {
        $messenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
        $messages = $messenger->getMessages();
        $messenger->clearMessages();

        if (count($messages) > 0) {
            return implode("<br />", $messages);
        }

        return null;
    }
}