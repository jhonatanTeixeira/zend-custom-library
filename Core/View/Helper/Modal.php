<?php
class Core_View_Helper_Modal extends Zend_Dojo_View_Helper_CustomDijit
{
    public function modal($id, $content, $attribs = array(), $title = "info")
    {
        $this->view->inlineScript()
            ->appendScript("dojo.ready(function(){dijit.byId('$id').show()})");

        return $this->customDijit(
            $id,
            $content,
            array(
                'dojoType' => 'dijit.Dialog',
                'title' => $title,
                'region' => 'center'
            ),
            $attribs
        );
    }

}